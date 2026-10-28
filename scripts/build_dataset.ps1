# Script para gerar todos os 386 Pokémons das Gerações 1, 2 e 3
# com modelos 3D em PNG transparente (Pokémon Home 3D Renders)

$ErrorActionPreference = 'Stop'
$outputPath = Join-Path $PSScriptRoot "..\database\pokemons_seed.json"
$sqlPath = Join-Path $PSScriptRoot "..\database\seed.sql"

Write-Host "Baixando catálogo completo de Pokémons..." -ForegroundColor Cyan
$sourceUrl = "https://raw.githubusercontent.com/fanzeyi/pokemon.json/master/pokedex.json"
$pokedexData = Invoke-RestMethod -Uri $sourceUrl -UseBasicParsing

Write-Host "Total de registros recebidos da fonte: $($pokedexData.Count)" -ForegroundColor Green

$gen1to3 = @()

foreach ($item in $pokedexData) {
    $natId = [int]$item.id
    # Apenas 1ª a 3ª Geração: 1 até 386
    if ($natId -lt 1 -or $natId -gt 386) {
        continue
    }

    $name = $item.name.english
    $slug = ($name.ToLower() -replace '[^a-z0-9]', '-').Trim('-')

    $type1 = $item.type[0]
    $type2 = if ($item.type.Count -gt 1) { $item.type[1] } else { $null }

    $hp = [int]$item.base.HP
    $atk = [int]$item.base.Attack
    $def = [int]$item.base.Defense
    $spAtk = [int]$item.base.'Sp. Attack'
    $spDef = [int]$item.base.'Sp. Defense'
    $spd = [int]$item.base.Speed
    $total = $hp + $atk + $def + $spAtk + $spDef + $spd

    # Modelo 3D com fundo transparente em alta definição PNG (Pokémon HOME 3D)
    $imageUrl = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/home/$natId.png"

    # Determina a geração
    $genText = if ($natId -le 151) { "1ª Geração (Kanto)" } elseif ($natId -le 251) { "2ª Geração (Johto)" } else { "3ª Geração (Hoenn)" }

    $desc = "Pokémon número {0:D3} da {1}. Apresenta total de {2} pontos de atributos básicos no tipo {3}{4}." -f $natId, $genText, $total, $type1, $(if ($type2) { " e $type2" } else { "" })

    $pokemonObj = [ordered]@{
        id = $natId
        national_id = $natId
        name = $name
        slug = $slug
        type1 = $type1
        type2 = $type2
        total = $total
        hp = $hp
        attack = $atk
        defense = $def
        sp_atk = $spAtk
        sp_def = $spDef
        speed = $spd
        image_url = $imageUrl
        is_secret = 0
        description = $desc
    }

    $gen1to3 += $pokemonObj
}

# Adiciona o Pokémon Secreto Obrigatório (Requisitos: 0,0000001% - Fantasma do Comunismo)
$secretPokemon = [ordered]@{
    id = 9999
    national_id = 9999
    name = "Fantasma do Comunismo"
    slug = "fantasma-do-comunismo"
    type1 = "Ghost"
    type2 = "Revolutionary"
    total = 1917
    hp = 200
    attack = 191
    defense = 191
    sp_atk = 200
    sp_def = 200
    speed = 180
    image_url = "assets/img/fantasma_comunismo.svg"
    is_secret = 1
    description = "Um espectro que ronda a Pokédex! Que as classes dominantes tremam diante da revolução dos Pokémons! Os treinadores nada têm a perder a não ser suas correntes. Têm um mundo a ganhar. Proletários de todos os continentes, uni-vos!"
}

$gen1to3 += $secretPokemon

Write-Host "Salvando $($gen1to3.Count) Pokémons no arquivo JSON..." -ForegroundColor Cyan
$jsonContent = ConvertTo-Json $gen1to3 -Depth 5
[System.IO.File]::WriteAllText($outputPath, $jsonContent, [System.Text.Encoding]::UTF8)

Write-Host "Gerando script SQL (seed.sql)..." -ForegroundColor Cyan
$sqlLines = @(
    "USE `pokedex_db`;",
    "",
    "-- Limpa registros existentes para carga completa",
    "TRUNCATE TABLE `pokemons`;",
    "",
    "INSERT INTO `pokemons` (`national_id`, `name`, `slug`, `type1`, `type2`, `total`, `hp`, `attack`, `defense`, `sp_atk`, `sp_def`, `speed`, `image_url`, `is_secret`, `description`) VALUES"
)

$valuesList = @()
foreach ($p in $gen1to3) {
    $escName = $p.name.Replace("'", "''")
    $escSlug = $p.slug.Replace("'", "''")
    $escType1 = $p.type1.Replace("'", "''")
    $escType2 = if ($p.type2) { "'$($p.type2.Replace("'", "''"))'" } else { "NULL" }
    $escImg = $p.image_url.Replace("'", "''")
    $escDesc = $p.description.Replace("'", "''")

    $line = "({0}, '{1}', '{2}', '{3}', {4}, {5}, {6}, {7}, {8}, {9}, {10}, {11}, '{12}', {13}, '{14}')" -f `
        $p.national_id, $escName, $escSlug, $escType1, $escType2, $p.total, $p.hp, $p.attack, $p.defense, $p.sp_atk, $p.sp_def, $p.speed, $escImg, $p.is_secret, $escDesc

    $valuesList += $line
}

$sqlLines += ($valuesList -join ",`n") + ";"

[System.IO.File]::WriteAllText($sqlPath, ($sqlLines -join "`n"), [System.Text.Encoding]::UTF8)

Write-Host "Concluído com sucesso! 386 Pokémons das Gens 1, 2 e 3 + Fantasma do Comunismo gerados!" -ForegroundColor Green
