# Servidor Localhost Nativo para Testes da Pokédex
# Compatível com PowerShell 5.1+ e Windows nativo

$port = 8000
$prefix = "http://localhost:$port/"
$root = $PSScriptRoot

$listener = New-Object System.Net.HttpListener
$listener.Prefixes.Add($prefix)

try {
    $listener.Start()
} catch {
    Write-Host "Porta 8000 ocupada. Alternando para 8080..." -ForegroundColor Yellow
    $port = 8080
    $prefix = "http://localhost:$port/"
    $listener = New-Object System.Net.HttpListener
    $listener.Prefixes.Add($prefix)
    $listener.Start()
}

Write-Host "==========================================================" -ForegroundColor Green
Write-Host "   POKEDEX LOCALHOST SERVER ATIVO COM SUCESSO!" -ForegroundColor Cyan
Write-Host "   Acesse: $prefix" -ForegroundColor Yellow
Write-Host "   Pressione Ctrl+C para encerrar o servidor" -ForegroundColor Gray
Write-Host "==========================================================" -ForegroundColor Green

$session = @{
    scanned_pokemons = @()
    total_scans = 0
}

$seedPath = Join-Path $root "database\pokemons_seed.json"
$pokemonsJson = if (Test-Path $seedPath) { Get-Content $seedPath -Raw -Encoding UTF8 } else { "[]" }
$pokemons = ConvertFrom-Json $pokemonsJson

# Abre navegador automaticamente
try {
    Start-Process $prefix
} catch {}

while ($listener.IsListening) {
    try {
        $context = $listener.GetContext()
        $request = $context.Request
        $response = $context.Response
        
        $urlPath = $request.Url.AbsolutePath

        $response.AddHeader("Access-Control-Allow-Origin", "*")
        $response.AddHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
        $response.AddHeader("Access-Control-Allow-Headers", "Content-Type")

        if ($request.HttpMethod -eq "OPTIONS") {
            $response.StatusCode = 200
            $response.Close()
            continue
        }

        # 1. API Pokemons (Busca Dinâmica, Sugestões, Filtros)
        if ($urlPath -match "^/api/pokemons(\.php)?") {
            $q = $request.QueryString["q"]
            $type = $request.QueryString["type"]
            $suggest = $request.QueryString["suggest"]
            
            $page = 1
            if ($request.QueryString["page"]) {
                [int]::TryParse($request.QueryString["page"], [ref]$page) | Out-Null
            }
            
            $limit = 24
            if ($suggest -eq "1") {
                $limit = 8
            }
            if ($request.QueryString["limit"]) {
                [int]::TryParse($request.QueryString["limit"], [ref]$limit) | Out-Null
            }

            $filtered = $pokemons | Where-Object {
                $p = $_
                if ($p.is_secret -eq 1 -and $q -ne "comunismo" -and $q -ne "fantasma" -and $q -ne "9999") {
                    return $false
                }
                if ($q) {
                    $term = $q.Trim().ToLower()
                    $nameMatch = $p.name.ToLower().Contains($term)
                    $idMatch = [string]$p.national_id -eq $term
                    if (-not $nameMatch -and -not $idMatch) { return $false }
                }
                if ($type -and $type -ne "all") {
                    $typeMatch = ($p.type1 -ieq $type) -or ($p.type2 -ieq $type)
                    if (-not $typeMatch) { return $false }
                }
                return $true
            }

            $totalItems = @($filtered).Count
            $offset = ($page - 1) * $limit
            $slice = @($filtered) | Select-Object -Skip $offset -First $limit

            $result = @{
                success = $true
                source = "powershell_localhost_simulator"
                page = $page
                limit = $limit
                total = $totalItems
                total_pages = [math]::Ceiling($totalItems / $limit)
                pokemons = $slice
            }

            $json = ConvertTo-Json $result -Depth 4
            $buffer = [System.Text.Encoding]::UTF8.GetBytes($json)
            $response.ContentType = "application/json; charset=utf-8"
            $response.ContentLength64 = $buffer.Length
            $response.OutputStream.Write($buffer, 0, $buffer.Length)
            $response.Close()
            continue
        }

        # 2. API Pokemon Individual
        if ($urlPath -match "^/api/pokemon(\.php)?") {
            $id = $request.QueryString["id"]
            $nationalId = $request.QueryString["national_id"]
            $slug = $request.QueryString["slug"]

            $found = $pokemons | Where-Object {
                if ($id -and $_.id -eq $id) { return $true }
                if ($nationalId -and $_.national_id -eq $nationalId) { return $true }
                if ($slug -and $_.slug -ieq $slug) { return $true }
                return $false
            } | Select-Object -First 1

            if ($found) {
                $json = ConvertTo-Json @{ success = $true; pokemon = $found } -Depth 4
                $response.StatusCode = 200
            } else {
                $json = ConvertTo-Json @{ success = $false; message = "Pokemon nao encontrado" }
                $response.StatusCode = 404
            }

            $buffer = [System.Text.Encoding]::UTF8.GetBytes($json)
            $response.ContentType = "application/json; charset=utf-8"
            $response.ContentLength64 = $buffer.Length
            $response.OutputStream.Write($buffer, 0, $buffer.Length)
            $response.Close()
            continue
        }

        # 3. API Scan
        if ($urlPath -match "^/api/scan(\.php)?") {
            $payload = @{}
            if ($request.HasEntityBody -and $request.ContentLength64 -gt 0) {
                $rawBytes = New-Object byte[] $request.ContentLength64
                $bytesRead = $request.InputStream.Read($rawBytes, 0, $rawBytes.Length)
                $body = [System.Text.Encoding]::UTF8.GetString($rawBytes, 0, $bytesRead)
                if ($body) {
                    $payload = ConvertFrom-Json $body -ErrorAction SilentlyContinue
                }
            }

            $session.total_scans++
            
            $forceSecret = ($payload.force_secret -eq "1917" -or $payload.force_secret -eq $true)
            $rolled = Get-Random -Minimum 1 -Maximum 100000000
            $isSecret = ($rolled -eq 1917 -or $forceSecret)

            $selected = $null
            if ($isSecret) {
                $selected = $pokemons | Where-Object { $_.national_id -eq 9999 } | Select-Object -First 1
            } elseif ($payload.pokemon_name) {
                $term = $payload.pokemon_name.Trim().ToLower()
                $selected = $pokemons | Where-Object { $_.name.ToLower().Contains($term) } | Select-Object -First 1
            }

            if (-not $selected) {
                $regulars = @($pokemons | Where-Object { -not $_.is_secret })
                $selected = $regulars | Get-Random
            }

            if ($selected -and -not ($session.scanned_pokemons -contains $selected.national_id)) {
                $session.scanned_pokemons += $selected.national_id
            }

            $msg = "Pokemon identificado: $($selected.name)!"
            if ($isSecret) {
                $msg = "ALERTA HISTORICO: O FANTASMA DO COMUNISMO FOI ENCONTRADO! VOCE FOI LIBERTO DO CAPITALISMO!"
            }

            $result = @{
                success = $true
                liberated_from_capitalism = $isSecret
                session_scanned_count = $session.scanned_pokemons.Count
                total_scans_made = $session.total_scans
                pokemon = $selected
                message = $msg
            }

            $json = ConvertTo-Json $result -Depth 4
            $buffer = [System.Text.Encoding]::UTF8.GetBytes($json)
            $response.ContentType = "application/json; charset=utf-8"
            $response.ContentLength64 = $buffer.Length
            $response.OutputStream.Write($buffer, 0, $buffer.Length)
            $response.Close()
            continue
        }

        # 4. API Sessão
        if ($urlPath -match "^/api/session(\.php)?") {
            if ($request.QueryString["action"] -eq "reset") {
                $session.scanned_pokemons = @()
                $session.total_scans = 0
            }

            $result = @{
                success = $true
                scanned_count = $session.scanned_pokemons.Count
                total_scans = $session.total_scans
                scanned_ids = $session.scanned_pokemons
                db_connected = $true
            }

            $json = ConvertTo-Json $result -Depth 3
            $buffer = [System.Text.Encoding]::UTF8.GetBytes($json)
            $response.ContentType = "application/json; charset=utf-8"
            $response.ContentLength64 = $buffer.Length
            $response.OutputStream.Write($buffer, 0, $buffer.Length)
            $response.Close()
            continue
        }

        # 5. Scraper Endpoint
        if ($urlPath -match "^/(backend/)?scraper(\.php)?") {
            $result = @{
                success = $true
                total_scraped = $pokemons.Count
                message = "Scraper sincronizado com sucesso a partir de https://pokemondb.net/pokedex/all!"
            }
            $json = ConvertTo-Json $result
            $buffer = [System.Text.Encoding]::UTF8.GetBytes($json)
            $response.ContentType = "application/json; charset=utf-8"
            $response.ContentLength64 = $buffer.Length
            $response.OutputStream.Write($buffer, 0, $buffer.Length)
            $response.Close()
            continue
        }

        # Serviço de Arquivos Estáticos
        $relPath = $urlPath.TrimStart('/')
        if ([string]::IsNullOrWhiteSpace($relPath) -or $relPath -eq "index.html" -or $relPath -eq "index.php") {
            $relPath = "preview.html"
        }

        $filePath = Join-Path $root ($relPath -replace '/', '\')

        if (Test-Path $filePath -PathType Leaf) {
            $ext = [System.IO.Path]::GetExtension($filePath).ToLower()
            $mime = switch ($ext) {
                ".html" { "text/html; charset=utf-8" }
                ".htm"  { "text/html; charset=utf-8" }
                ".css"  { "text/css; charset=utf-8" }
                ".js"   { "application/javascript; charset=utf-8" }
                ".json" { "application/json; charset=utf-8" }
                ".svg"  { "image/svg+xml" }
                ".png"  { "image/png" }
                ".jpg"  { "image/jpeg" }
                ".jpeg" { "image/jpeg" }
                ".ico"  { "image/x-icon" }
                Default { "application/octet-stream" }
            }

            $bytes = [System.IO.File]::ReadAllBytes($filePath)
            $response.ContentType = $mime
            $response.ContentLength64 = $bytes.Length
            $response.OutputStream.Write($bytes, 0, $bytes.Length)
            $response.StatusCode = 200
        } else {
            $response.StatusCode = 404
            $msg = [System.Text.Encoding]::UTF8.GetBytes("404 Arquivo nao encontrado: $relPath")
            $response.OutputStream.Write($msg, 0, $msg.Length)
        }

        $response.Close()

    } catch {
        # Continua o loop sem encerrar o listener
    }
}
