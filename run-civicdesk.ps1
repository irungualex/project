$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$php = Join-Path $env:LOCALAPPDATA 'CivicDesk\runtime\php\php.exe'
$phpDirectory = Split-Path -Parent $php

if (-not (Test-Path $php)) {
    throw "The CivicDesk PHP runtime was not found at '$php'."
}

if (-not (Test-Path (Join-Path $projectRoot 'vendor\autoload.php'))) {
    throw "Laravel dependencies are missing. Run composer install from the project root first."
}

Push-Location $projectRoot
try {
    $env:Path = "$phpDirectory;$env:Path"
    if (-not (Test-Path 'database\database.sqlite')) {
        New-Item -ItemType File -Path 'database\database.sqlite' | Out-Null
    }

    $originalDatabaseUrl = $env:DATABASE_URL
    $configuredDatabaseUrl = $originalDatabaseUrl

    if ([string]::IsNullOrWhiteSpace($configuredDatabaseUrl)) {
        $envFile = Join-Path $projectRoot '.env'
        if (Test-Path $envFile) {
            $databaseUrlLine = Get-Content $envFile | Where-Object { $_ -match '^DATABASE_URL=' } | Select-Object -First 1
            if ($databaseUrlLine) {
                $configuredDatabaseUrl = $databaseUrlLine.Substring('DATABASE_URL='.Length).Trim()
                if ($configuredDatabaseUrl.Length -ge 2 -and $configuredDatabaseUrl.StartsWith('"') -and $configuredDatabaseUrl.EndsWith('"')) {
                    $configuredDatabaseUrl = $configuredDatabaseUrl.Substring(1, $configuredDatabaseUrl.Length - 2)
                }
            }
        }
    }

    if (-not [string]::IsNullOrWhiteSpace($configuredDatabaseUrl)) {
        $databaseUri = $null
        if ([Uri]::TryCreate($configuredDatabaseUrl, [UriKind]::Absolute, [ref]$databaseUri) -and $databaseUri.Host.Contains('-pooler.')) {
            $directHost = $databaseUri.Host -replace '-pooler(?=\.)', ''
            $migrationDatabaseUrl = $configuredDatabaseUrl.Replace($databaseUri.Host, $directHost)
            $env:DATABASE_URL = $migrationDatabaseUrl
            Write-Host 'Using Neon direct connection for migrations; pooled URL will be restored for the app.'
        }
    }

    try {
        & $php artisan migrate --force
    }
    finally {
        if ($null -eq $originalDatabaseUrl) {
            Remove-Item Env:DATABASE_URL -ErrorAction SilentlyContinue
        } else {
            $env:DATABASE_URL = $originalDatabaseUrl
        }
    }

    if ($LASTEXITCODE -ne 0) {
        throw 'Database migrations failed. Check the error above before starting CivicDesk.'
    }

    & $php artisan serve --host=127.0.0.1 --port=8000
    if ($LASTEXITCODE -ne 0) {
        throw 'The CivicDesk development server stopped unexpectedly.'
    }
}
finally {
    Pop-Location
}
