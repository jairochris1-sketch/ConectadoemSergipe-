import os
import sys
import ftplib

FTP_HOST = '147.93.14.92'
FTP_USER = 'u361083290.jairosergipe'
FTP_PASS = 'U6xQ3=g32l*'

def deploy():
    print("[DEPLOY] Iniciando sincronizacao de arquivos para a Hostinger...")
    try:
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        ftp.cwd('/')
        
        files_to_sync = [
            'app/Views/layouts/main.php',
            'app/Modules/Core/Views/home.php',
            'public/css/style.css'
        ]
        
        for file_path in files_to_sync:
            if os.path.exists(file_path):
                print(f" -> Enviando: {file_path}")
                with open(file_path, 'rb') as f:
                    ftp.storbinary(f'STOR {file_path}', f)
        
        ftp.quit()
        print("[DEPLOY] Sincronizacao concluida com sucesso!")
    except Exception as e:
        print(f"[ERRO] Falha durante o deploy: {e}")
        sys.exit(1)

if __name__ == '__main__':
    deploy()
