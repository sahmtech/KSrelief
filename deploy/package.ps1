$ErrorActionPreference = "Stop"

$root = Split-Path -Parent $PSScriptRoot
$staging = Join-Path $root "deploy-staging"
$zipPath = Join-Path $root "esghaa-deploy.zip"

$includeDirs = @(
    "app", "bootstrap", "config", "database", "deploy", "lang",
    "public", "resources", "routes", "storage"
)
$includeFiles = @(
    "artisan", "composer.json", "composer.lock",
    ".env.cloudways.example", "deploy\post-deploy.sh"
)

Write-Host "Building frontend assets..."
Push-Location $root
npm run build | Out-Null
Pop-Location

if (Test-Path $staging) { Remove-Item $staging -Recurse -Force }
New-Item -ItemType Directory -Path $staging | Out-Null

foreach ($dir in $includeDirs) {
    $source = Join-Path $root $dir
    if (Test-Path $source) {
        Copy-Item $source (Join-Path $staging $dir) -Recurse -Force
    }
}

foreach ($file in $includeFiles) {
    $source = Join-Path $root $file
    if (Test-Path $source) {
        $destDir = Join-Path $staging (Split-Path $file -Parent)
        if ($destDir -and -not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        Copy-Item $source (Join-Path $staging $file) -Force
    }
}

# Clean runtime/cache from package
@(
    "storage\logs\*.log",
    "storage\framework\cache\data\*",
    "storage\framework\sessions\*",
    "storage\framework\views\*.php",
    "bootstrap\cache\*.php"
) | ForEach-Object {
    Get-Item (Join-Path $staging $_) -ErrorAction SilentlyContinue | Remove-Item -Force -Recurse -ErrorAction SilentlyContinue
}

if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
Compress-Archive -Path (Join-Path $staging "*") -DestinationPath $zipPath -CompressionLevel Optimal

Remove-Item $staging -Recurse -Force

Write-Host ""
Write-Host "Package ready: $zipPath"
Write-Host "Upload to Cloudways public_html, extract, then run deploy/post-deploy.sh"
