SET working_dir="\tmp"
SET backup_dir="\\CNCVEEAM\ImageShare\CNCCRM_Raw_Backup"
SET gzip_dir="C:\cygwin\bin"

C:

rem mysqldump cncapps -u root --password=CnC1989 > %working_dir%\mysqldump-%date:~0,2%%date:~-7,2%%date:~-4,4%.sql

mysqldump cncapps -u root --password=CnC1989 > %backup_dir%\mysqldump-%date:~0,2%%date:~-7,2%%date:~-4,4%.sql

rem cd %working_dir%

%gzip_dir%\gzip.exe %backup_dir%\mysqldump-%date:~0,2%%date:~-7,2%%date:~-4,4%.sql -f

rem move mysqldump-%date:~0,2%%date:~-7,2%%date:~-4,4%.sql.gz %backup_dir%