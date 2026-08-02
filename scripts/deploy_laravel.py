import os
import sys
import ftplib

FTP_HOST = '147.93.14.92'
FTP_USER = 'u361083290.jairosergipe'
FTP_PASS = 'U6xQ3=g32l*'

def deploy_laravel():
    print("[DEPLOY LARAVEL] Sincronizando estrutura Laravel para Hostinger...")
    try:
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        ftp.cwd('/')

        # Sincronizar arquivos principais da aplicação Laravel
        files_to_sync = [
            'laravel-app/routes/web.php',
            'laravel-app/app/Http/Controllers/HomeController.php',
            'laravel-app/app/Http/Controllers/AdController.php',
            'laravel-app/app/Models/User.php',
            'laravel-app/app/Models/Ad.php',
            'laravel-app/resources/views/layouts/app.blade.php',
            'laravel-app/public/css/style.css'
        ]

        for file_path in files_to_sync:
            if os.path.exists(file_path):
                print(f" -> Enviando: {file_path}")
                with open(file_path, 'rb') as f:
                    ftp.storbinary(f'STOR {file_path}', f)

        ftp.quit()
        print("[DEPLOY LARAVEL] Concluido com sucesso!")
    except Exception as e:
        print(f"[ERRO] Falha ao enviar Laravel: {e}")

if __name__ == '__main__':
    deploy_laravel()
