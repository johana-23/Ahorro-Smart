$env:JDK_JAVA_OPTIONS = "--enable-native-access=ALL-UNNAMED --sun-misc-unsafe-memory-access=allow"
#Requires -Version 5.1
$ErrorActionPreference = "Stop"

$RootDir    = Split-Path -Parent $PSScriptRoot
$EnvFile    = Join-Path $RootDir "smart-ahorro.env"
$RuntimeDir = Join-Path $RootDir ".runtime"
$PidFile    = Join-Path $RuntimeDir "pids.json"

if (-not (Test-Path $RuntimeDir)) {
    New-Item -ItemType Directory -Path $RuntimeDir -Force | Out-Null
}

function Write-Banner {
    Write-Host "============================================================" -ForegroundColor Cyan
    Write-Host "   SMART AHORRO - EJECUCION CENTRALIZADA                    " -ForegroundColor Cyan
    Write-Host "   Laravel (PHP) + Spring Boot (Java) | Una sola terminal   " -ForegroundColor Cyan
    Write-Host "============================================================" -ForegroundColor Cyan
}

function Read-KeyValueFile([string]$Path) {
    $map = @{}
    if (Test-Path $Path) {
        Get-Content $Path -Encoding UTF8 | ForEach-Object {
            $line = $_.Trim()
            if ($line -and -not $line.StartsWith("#") -and $line.Contains("=")) {
                $parts = $line.Split("=", 2)
                $key = $parts[0].Trim()
                $val = $parts[1].Trim().Trim('"').Trim("'")
                $map[$key] = $val
            }
        }
    }
    return $map
}

function Find-ComponentDir([string]$BaseDir, [string]$MarkerFile, [string]$Configured) {
    if ($Configured -and $Configured -ne "AUTO") {
        $candidate = if ([System.IO.Path]::IsPathRooted($Configured)) { $Configured } else { Join-Path $BaseDir $Configured }
        if (Test-Path (Join-Path $candidate $MarkerFile)) {
            return (Resolve-Path $candidate).Path
        }
    }
    if (Test-Path (Join-Path $BaseDir $MarkerFile)) {
        return (Resolve-Path $BaseDir).Path
    }
    $found = Get-ChildItem -Path $BaseDir -Directory -Depth 2 -ErrorAction SilentlyContinue |
        Where-Object { $_.FullName -notmatch '\\(vendor|node_modules|target|\.git|\.runtime)\\' } |
        Where-Object { Test-Path (Join-Path $_.FullName $MarkerFile) } |
        Select-Object -First 1
    if ($found) { return $found.FullName }
    return $null
}

function Resolve-PhpBin([string]$Configured) {
    if ($Configured -and $Configured -ne "AUTO" -and (Test-Path $Configured)) { return $Configured }
    $cmd = Get-Command php -ErrorAction SilentlyContinue
    if ($cmd) { return $cmd.Source }
    if (Test-Path "C:\xampp\php\php.exe") { return "C:\xampp\php\php.exe" }
    return $null
}

function Resolve-JavaBin {
    $cmd = Get-Command java -ErrorAction SilentlyContinue
    if ($cmd) { return $cmd.Source }
    if ($env:JAVA_HOME -and (Test-Path "$env:JAVA_HOME\bin\java.exe")) { return "$env:JAVA_HOME\bin\java.exe" }
    return $null
}

function Resolve-MavenBin([string]$SpringDir, [string]$Configured) {
    if ($Configured -and $Configured -ne "AUTO" -and (Test-Path $Configured)) { return $Configured }
    $mvnw = Join-Path $SpringDir "mvnw.cmd"
    if (Test-Path $mvnw) { return $mvnw }
    $cmd = Get-Command mvn -ErrorAction SilentlyContinue
    if ($cmd) { return $cmd.Source }

    $localMvn = Join-Path $RuntimeDir "maven\apache-maven-3.9.9\bin\mvn.cmd"
    if (Test-Path $localMvn) { return $localMvn }

    Write-Host "  [INFO] Maven no encontrado en PATH. Descargando Apache Maven 3.9.9 portable en .runtime\maven..." -ForegroundColor Yellow
    $mavenBase = Join-Path $RuntimeDir "maven"
    New-Item -ItemType Directory -Path $mavenBase -Force | Out-Null
    $zipPath = Join-Path $mavenBase "maven.zip"
    $url = "https://archive.apache.org/dist/maven/maven-3/3.9.9/binaries/apache-maven-3.9.9-bin.zip"
    try {
        [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
        Invoke-WebRequest -Uri $url -OutFile $zipPath -UseBasicParsing
        Expand-Archive -Path $zipPath -DestinationPath $mavenBase -Force
        Remove-Item $zipPath -Force -ErrorAction SilentlyContinue
        if (Test-Path $localMvn) { return $localMvn }
    } catch {
        Write-Host "  [ERROR] No se pudo descargar Maven automaticamente: $($_.Exception.Message)" -ForegroundColor Red
    }
    return $null
}

function Get-PortStatus([int]$Port) {
    $conn = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($conn) {
        $proc = Get-Process -Id $conn.OwningProcess -ErrorAction SilentlyContinue
        return @{
            InUse = $true
            PID   = $conn.OwningProcess
            Name  = if ($proc) { $proc.ProcessName } else { "Proceso desconocido" }
        }
    }
    return @{ InUse = $false; PID = 0; Name = "" }
}

function Test-TcpOpen([string]$TargetHost, [int]$Port) {
    try {
        $client = New-Object System.Net.Sockets.TcpClient
        $ar = $client.BeginConnect($TargetHost, $Port, $null, $null)
        $connected = $ar.AsyncWaitHandle.WaitOne(400, $false)
        if ($connected -and $client.Connected) {
            $client.EndConnect($ar)
            $client.Close()
            return $true
        }
        $client.Close()
        return $false
    } catch {
        return $false
    }
}

function Stop-ProcessTree([int]$TargetPid) {
    if ($TargetPid -gt 0) {
        try {
            $p = Get-Process -Id $TargetPid -ErrorAction SilentlyContinue
            if ($p) { & taskkill.exe /PID $TargetPid /T /F 2>&1 | Out-Null }
        } catch {}
    }
}

function Start-ChildProcess {
    param(
        [string]$Tag,
        [string]$Color,
        [string]$Executable,
        [string]$Arguments,
        [string]$WorkDir,
        [hashtable]$EnvVars
    )

    $psi = New-Object System.Diagnostics.ProcessStartInfo
    $psi.FileName = $Executable
    $psi.Arguments = $Arguments
    $psi.WorkingDirectory = $WorkDir
    $psi.UseShellExecute = $false
    $psi.RedirectStandardOutput = $true
    $psi.RedirectStandardError = $true
    $psi.CreateNoWindow = $true
    $psi.StandardOutputEncoding = [System.Text.Encoding]::UTF8
    $psi.StandardErrorEncoding  = [System.Text.Encoding]::UTF8

    if ($EnvVars) {
        foreach ($k in $EnvVars.Keys) {
            $psi.EnvironmentVariables[$k] = [string]$EnvVars[$k]
        }
    }

    $proc = New-Object System.Diagnostics.Process
    $proc.StartInfo = $psi
    $proc.EnableRaisingEvents = $true

    $onOut = {
        if (-not [string]::IsNullOrWhiteSpace($EventArgs.Data)) {
            Write-Host "[$($Event.MessageData.Tag)] $($EventArgs.Data)" -ForegroundColor $Event.MessageData.Color
        }
    }
    $onErr = {
        if (-not [string]::IsNullOrWhiteSpace($EventArgs.Data)) {
            if ($EventArgs.Data -match '^NOTE:\s+Picked up JDK_JAVA_OPTIONS') { return }
            Write-Host "[$($Event.MessageData.Tag) | ERR] $($EventArgs.Data)" -ForegroundColor Red
        }
    }

    $state = @{ Tag = $Tag; Color = $Color }
    $subOut = Register-ObjectEvent -InputObject $proc -EventName "OutputDataReceived" -Action $onOut -MessageData $state
    $subErr = Register-ObjectEvent -InputObject $proc -EventName "ErrorDataReceived"  -Action $onErr -MessageData $state

    [void]$proc.Start()
    $proc.BeginOutputReadLine()
    $proc.BeginErrorReadLine()

    return @{
        Process = $proc
        SubOut  = $subOut
        SubErr  = $subErr
    }
}

Write-Banner
$cfg = Read-KeyValueFile $EnvFile

$LaravelDir = Find-ComponentDir -BaseDir $RootDir -MarkerFile "artisan" -Configured $cfg["LARAVEL_DIR"]
$SpringDir  = Find-ComponentDir -BaseDir $RootDir -MarkerFile "pom.xml" -Configured $cfg["SPRING_DIR"]

if (-not $LaravelDir) {
    Write-Host "[ERROR CRITICO] No se encontro el proyecto Laravel (artisan)." -ForegroundColor Red
    exit 1
}
if (-not $SpringDir) {
    Write-Host "[ERROR CRITICO] No se encontro el proyecto Spring Boot (pom.xml)." -ForegroundColor Red
    exit 1
}

$laravelEnv = Read-KeyValueFile (Join-Path $LaravelDir ".env")
function Resolve-Setting([string]$Key, [string]$DefaultVal) {
    $v = $cfg[$Key]
    if ($v -and $v -ne "AUTO") { return $v }
    if ($laravelEnv.ContainsKey($Key) -and $laravelEnv[$Key] -ne "") { return $laravelEnv[$Key] }
    return $DefaultVal
}

$LaravelHost    = Resolve-Setting "LARAVEL_HOST" "127.0.0.1"
$LaravelPort    = [int](Resolve-Setting "LARAVEL_PORT" "8000")
$SpringHost     = Resolve-Setting "SPRING_HOST" "127.0.0.1"
$SpringPort     = [int](Resolve-Setting "SPRING_PORT" "8080")
$DbHost         = Resolve-Setting "DB_HOST" "127.0.0.1"
$DbPort         = [int](Resolve-Setting "DB_PORT" "3306")
$DbDatabase     = Resolve-Setting "DB_DATABASE" "smart_ahorro_ia"
$DbUsername     = Resolve-Setting "DB_USERNAME" "root"
$DbPassword     = if ($cfg["DB_PASSWORD"] -and $cfg["DB_PASSWORD"] -ne "AUTO") { $cfg["DB_PASSWORD"] } elseif ($laravelEnv.ContainsKey("DB_PASSWORD")) { $laravelEnv["DB_PASSWORD"] } else { "" }
$LaravelTimeout = [int](Resolve-Setting "LARAVEL_STARTUP_TIMEOUT" "20")
$SpringTimeout  = [int](Resolve-Setting "SPRING_STARTUP_TIMEOUT" "60")

Write-Host "`n[1/4] Arquitectura detectada:" -ForegroundColor White
Write-Host "  -> Laravel     : $LaravelDir (Puerto $LaravelPort)"
Write-Host "  -> Spring Boot : $SpringDir (Puerto $SpringPort)"
Write-Host "  -> MySQL DB    : $DbDatabase en $DbHost`:$DbPort (Usuario: $DbUsername)"

Write-Host "`n[2/4] Verificando MySQL..." -ForegroundColor White
if (Test-TcpOpen -TargetHost $DbHost -Port $DbPort) {
    Write-Host "  [OK] MySQL activo en $DbHost`:$DbPort." -ForegroundColor Green
} else {
    Write-Host "  [ADVERTENCIA] MySQL no responde en $DbHost`:$DbPort. Enciende MySQL en XAMPP." -ForegroundColor Yellow
}

$laravelSvc = $null
$springSvc  = $null
$laravelUp  = $false
$springUp   = $false

try {
    Write-Host "`n[3/4] Iniciando servicios en paralelo..." -ForegroundColor White

    $phpBin = Resolve-PhpBin $cfg["PHP_BIN"]
    $port8000 = Get-PortStatus -Port $LaravelPort
    if (-not $phpBin) {
        Write-Host "  [ERROR LARAVEL] No se encontro php.exe." -ForegroundColor Red
    } elseif ($port8000.InUse) {
        Write-Host "  [CONFLICTO PUERTO] Puerto $LaravelPort ocupado por '$($port8000.Name)' (PID $($port8000.PID))." -ForegroundColor Yellow
    } else {
        $laravelSvc = Start-ChildProcess `
            -Tag "LARAVEL" `
            -Color "Green" `
            -Executable $phpBin `
            -Arguments "artisan serve --host=$LaravelHost --port=$LaravelPort" `
            -WorkDir $LaravelDir `
            -EnvVars @{
                "SPRING_API_URL"     = "http://$SpringHost`:$SpringPort/api"
                "SPRING_API_TIMEOUT" = "5"
            }
    }

    $javaBin = Resolve-JavaBin
    $mvnBin  = Resolve-MavenBin -SpringDir $SpringDir -Configured $cfg["MAVEN_BIN"]
    $port8080 = Get-PortStatus -Port $SpringPort
    if (-not $javaBin) {
        Write-Host "  [ERROR SPRING] No se encontro Java (JDK) en PATH ni JAVA_HOME." -ForegroundColor Red
    } elseif (-not $mvnBin) {
        Write-Host "  [ERROR SPRING] No se encontro Maven." -ForegroundColor Red
    } elseif ($port8080.InUse) {
        Write-Host "  [CONFLICTO PUERTO] Puerto $SpringPort ocupado por '$($port8080.Name)' (PID $($port8080.PID))." -ForegroundColor Yellow
    } else {
        $mvnArgs = "/c `"`"$mvnBin`" spring-boot:run `"-Dspring-boot.run.profiles=integration`"`""
        $springSvc = Start-ChildProcess `
            -Tag "SPRING " `
            -Color "Magenta" `
            -Executable "cmd.exe" `
            -Arguments $mvnArgs `
            -WorkDir $SpringDir `
            -EnvVars @{
                "SPRING_PORT" = "$SpringPort"
                "DB_HOST"     = "$DbHost"
                "DB_PORT"     = "$DbPort"
                "DB_DATABASE" = "$DbDatabase"
                "DB_USERNAME" = "$DbUsername"
                "DB_PASSWORD" = "$DbPassword"
            }
    }

    @{
        laravelPid  = if ($laravelSvc) { $laravelSvc.Process.Id } else { 0 }
        springPid   = if ($springSvc)  { $springSvc.Process.Id }  else { 0 }
        laravelPort = $LaravelPort
        springPort  = $SpringPort
    } | ConvertTo-Json | Set-Content -Path $PidFile -Encoding UTF8

    Write-Host "`n[4/4] Comprobando disponibilidad de puertos..." -ForegroundColor White
    $maxWait = [Math]::Max($LaravelTimeout, $SpringTimeout)
    for ($s = 1; $s -le $maxWait; $s++) {
        Start-Sleep -Seconds 1

        if ($laravelSvc -and -not $laravelUp -and -not $laravelSvc.Process.HasExited) {
            if (Test-TcpOpen -TargetHost $LaravelHost -Port $LaravelPort) {
                $laravelUp = $true
                Write-Host ">>> [LISTO] Laravel escuchando en http://$LaravelHost`:$LaravelPort <<<" -ForegroundColor Green
            }
        }

        if ($springSvc -and -not $springUp -and -not $springSvc.Process.HasExited) {
            if (Test-TcpOpen -TargetHost $SpringHost -Port $SpringPort) {
                $springUp = $true
                Write-Host ">>> [LISTO] Spring Boot escuchando en http://$SpringHost`:$SpringPort <<<" -ForegroundColor Magenta
            }
        }

        $lReady = (-not $laravelSvc) -or $laravelUp -or $laravelSvc.Process.HasExited -or ($s -ge $LaravelTimeout)
        $sReady = (-not $springSvc)  -or $springUp  -or $springSvc.Process.HasExited  -or ($s -ge $SpringTimeout)
        if ($lReady -and $sReady) { break }
    }

    Write-Host "`n============================================================" -ForegroundColor Cyan
    Write-Host " RESUMEN DE EJECUCION - SMART AHORRO" -ForegroundColor Cyan
    if ($laravelSvc) {
        if ($laravelUp) { Write-Host "  * Laravel (Web)      : ACTIVO -> http://$LaravelHost`:$LaravelPort" -ForegroundColor Green }
        else            { Write-Host "  * Laravel (Web)      : FALLO AL INICIAR (ver logs arriba)" -ForegroundColor Red }
    }
    if ($springSvc) {
        if ($springUp)  { Write-Host "  * Spring Boot (API)  : ACTIVO -> http://$SpringHost`:$SpringPort/api/health" -ForegroundColor Green }
        else            { Write-Host "  * Spring Boot (API)  : FALLO AL INICIAR (ver logs arriba)" -ForegroundColor Red }
    }
    Write-Host " Presiona Ctrl + C para detener ambos servicios de forma segura." -ForegroundColor Yellow
    Write-Host "============================================================`n" -ForegroundColor Cyan

    $lNotified = $false
    $sNotified = $false
    while ($true) {
        Start-Sleep -Seconds 2
        if ($laravelSvc -and $laravelSvc.Process.HasExited -and -not $lNotified) {
            $lNotified = $true
            Write-Host "`n[ALERTA] Laravel finalizo (codigo $($laravelSvc.Process.ExitCode)). Spring Boot sigue operando." -ForegroundColor Red
        }
        if ($springSvc -and $springSvc.Process.HasExited -and -not $sNotified) {
            $sNotified = $true
            Write-Host "`n[ALERTA] Spring Boot finalizo (codigo $($springSvc.Process.ExitCode)). Laravel sigue operando." -ForegroundColor Red
        }
        if (((-not $laravelSvc) -or $laravelSvc.Process.HasExited) -and ((-not $springSvc) -or $springSvc.Process.HasExited)) {
            break
        }
    }
}
finally {
    Write-Host "`n[CIERRE] Deteniendo servicios de Smart Ahorro..." -ForegroundColor Yellow
    if ($laravelSvc) {
        Stop-ProcessTree -TargetPid $laravelSvc.Process.Id
        Unregister-Event -SourceIdentifier $laravelSvc.SubOut.Name -ErrorAction SilentlyContinue
        Unregister-Event -SourceIdentifier $laravelSvc.SubErr.Name -ErrorAction SilentlyContinue
    }
    if ($springSvc) {
        Stop-ProcessTree -TargetPid $springSvc.Process.Id
        Unregister-Event -SourceIdentifier $springSvc.SubOut.Name -ErrorAction SilentlyContinue
        Unregister-Event -SourceIdentifier $springSvc.SubErr.Name -ErrorAction SilentlyContinue
    }
    if (Test-Path $PidFile) { Remove-Item $PidFile -Force -ErrorAction SilentlyContinue }
    Write-Host "[CIERRE] Todos los procesos fueron cerrados correctamente." -ForegroundColor Green
}
