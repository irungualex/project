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

    & $php artisan migrate --force
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
