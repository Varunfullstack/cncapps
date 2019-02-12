UPDATE PASSWORD
SET pas_service = TRIM(pas_service);
UPDATE PASSWORD
SET pas_service = CASE pas_service
                    WHEN 'Domain Administrator' THEN 'DOMAIN ADMIN'
                    WHEN 'Domain Administrator Account' THEN 'DOMAIN ADMIN'
                    WHEN 'Domain Admin Account' THEN 'DOMAIN ADMIN'
                    WHEN 'Domain Administrator Service Account' THEN 'Domain Admin Service Account'
                    WHEN 'Domain service account' THEN 'Domain Admin Service Account'
                    WHEN 'CNC admin account' THEN 'CNC Domain Admin'
                    WHEN 'CNC Support Account' THEN 'CNC Domain Admin'
                    WHEN 'CNC Remote Support' THEN 'CNC Domain Admin'
                    WHEN 'CNC Admin' THEN 'CNC Domain Admin'
                    WHEN 'CNC Domain Administrator' THEN 'CNC Domain Admin'
                    WHEN 'DOMAIN ADMIN CNC' THEN 'CNC Domain Admin'
                    WHEN 'CNC Domain Admin account' THEN 'CNC Domain Admin'
                    WHEN 'Cncadmin' THEN 'CNC Domain Admin'
                    WHEN 'CNC Administrator account' THEN 'CNC Domain Admin'
                    WHEN 'cncserviceaccount' THEN 'CNC service account'
                    WHEN 'CNC Serviceaccount' THEN 'CNC service account'
                    WHEN 'CNC Domain Admin Service Accounts' THEN 'CNC service account'
                    WHEN 'APC UPS Network Management card' THEN 'APC NIC'
                    WHEN 'APC UPS' THEN 'APC NIC'
                    WHEN 'APC Network Card' THEN 'APC NIC'
                    WHEN 'APC PCNS and APC NIC' THEN 'APC NIC'
                    WHEN 'APC Powerchute Network Management Card' THEN 'APC NIC'
                    WHEN 'APC UPS Management' THEN 'APC NIC'
                    WHEN 'APC UPS Management Cards' THEN 'APC NIC'
                    WHEN 'APC UPS NIC' THEN 'APC NIC'
                    WHEN 'APC Web management board' THEN 'APC NIC'
                    WHEN 'APC login' THEN 'APC PowerChute'
                    WHEN 'APC Smart UPS' THEN 'APC PowerChute'
                    WHEN 'APC PowerChute Console' THEN 'APC PowerChute'
                    WHEN 'APC Admin' THEN 'APC PowerChute'
                    WHEN 'APC PCNS' THEN 'APC PowerChute'
                    WHEN 'APC PoweChute' THEN 'APC PowerChute'
                    WHEN 'APC Powerchute & UPS login' THEN 'APC PowerChute'
                    WHEN 'APC PowerChute & UPS NIC' THEN 'APC PowerChute'
                    WHEN 'APC Powerchute 3000va web console' THEN 'APC PowerChute'
                    WHEN 'APC PowerChute Login' THEN 'APC PowerChute'
                    WHEN 'APC Powerchute on MAIL1' THEN 'APC PowerChute'
                    WHEN 'APC PowerChute software' THEN 'APC PowerChute'
                    WHEN 'APC Powerchute UPS' THEN 'APC PowerChute'
                    WHEN 'APC PowerChute, NAS,' THEN 'APC PowerChute'
                    WHEN 'APC Software' THEN 'APC PowerChute'
                    WHEN 'APC UPS Login' THEN 'APC PowerChute'
                    WHEN 'APC UPS Login Account' THEN 'APC PowerChute'
                    WHEN 'APC UPS Software' THEN 'APC PowerChute'
                    WHEN 'APC UPSs' THEN 'APC PowerChute'
                    WHEN 'DRAC Card' THEN 'DRAC'
                    WHEN 'DRACs' THEN 'DRAC'
                    WHEN 'iDrac' THEN 'DRAC'
                    WHEN 'Office365 CNC admin account' THEN 'Office 365 CNC Admin'
                    WHEN 'Netgear Switches' THEN 'Netgear Switch'
                    WHEN 'Office 365 Admin Login' THEN 'Office 365 Admin Portal'
                    WHEN 'Office 365 Account' THEN 'Office 365 Admin Portal'
                    WHEN 'Office 365 Admin' THEN 'Office 365 Admin Portal'
                    WHEN 'Office 365 login' THEN 'Office 365 Admin Portal'
                    WHEN 'Office 365 - https://www.office.com/' THEN 'Office 365 Admin Portal'
                    WHEN 'Office 365 Administration Login' THEN 'Office 365 Admin Portal'
                    WHEN 'Office365 account' THEN 'Office 365 Admin Portal'
                    WHEN 'Office365 Admin' THEN 'Office 365 Admin Portal'
                    WHEN 'Office 365' THEN 'Office 365 Admin Portal'
                    WHEN 'Vmware' THEN 'VMWare ESXi Host'
                    WHEN 'VMware server' THEN 'VMWare ESXi Host'
                    WHEN 'vmware hosts' THEN 'VMWare ESXi Host'
                    WHEN 'vSphere/ESXi' THEN 'VMWare ESXi Host'
                    WHEN 'ESXi host' THEN 'VMWare ESXi Host'
                    WHEN 'Vsphere VMWare ESXi Admin Login' THEN 'VMWare ESXi Host'
                    WHEN 'Exclaimer Cloud Login' THEN 'Exclaimer Cloud'
                    WHEN 'Exclaimer Cloud Signatures for 365' THEN 'Exclaimer Cloud'
                    WHEN 'Exclaimer (CNC Reseller)' THEN 'Exclaimer Cloud'
                    WHEN 'Exclaimer Cloud Login for Signatures' THEN 'Exclaimer Cloud'
                    WHEN 'Local PC Admin' THEN 'Local PC Administrator account'
                    WHEN 'Local PC admin account' THEN 'Local PC Administrator account'
                    WHEN 'Local PC Administrator password' THEN 'Local PC Administrator account'
                    WHEN 'Local PC Password' THEN 'Local PC Administrator account'
                    WHEN 'PC Local Admin' THEN 'Local PC Administrator account'
                    WHEN 'Local PC Administrators' THEN 'Local PC Administrator account'
                    WHEN 'Local PC Account (edited) ' THEN 'Local PC Administrator account'
                    ELSE pas_service END;