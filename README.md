# Virtualization-Server-Project
Custom proxmox, apache guacamole, and php web app integration

Modified heavily from https://github.com/osc3b/proxmox-guacamole-client



## Dependencies:
* Proxmox VE: https://www.proxmox.com/en/proxmox-ve
* Apache Guacamole: 1.4 https://guacamole.apache.org/
* Apache Guacamole install script: https://github.com/MysticRyuujin/guac-install
* Corsinvest cv4pve-api-php (Current from github at 12/6 or any release after v7.2.1): https://github.com/Corsinvest/cv4pve-api-php
* Debian
* Apache2
* PHP 8.1
* Composer
* MariaDB (for Apache guacamole, MySQL server will work as well)

## Proxmox Setup
![ServerOverview](https://user-images.githubusercontent.com/54869540/206067646-b37aea86-6def-4bfe-8c03-5f0e48420975.png)

3 Debian containers: One gets Apache Guacamole with MariaDB, next gets apache2 webserver with php and normal requirements, last is a DHCP server for private VM network.

## Network Overview
This is for a basic one interface server. If more interfaces are available VM network should have it’s own interface.
![networkInfo](https://user-images.githubusercontent.com/54869540/206068945-06400211-a80f-42d1-b9e2-9447d592aa25.png)

## Database Overview
Apache Guacamole database is unaltered. TODO: upload mysqldump of database structure
![databaseOverview](https://user-images.githubusercontent.com/54869540/206069606-3d887a57-2a86-4557-960b-5a40b8b85f7d.png)

## Lab Setup
VM Template Setup
1. Download ISO in proxmox 
2. Create VM make sure bridge is set to vmbr1 and storage is vm stogage
4. Modify VM to hearts content
5. Convert to VM template
6. In Admin Home Page go to VM Template Setup
7. Select VM id # from current VMs on Proxmox
8. Enter id in text box and submit

User and group setup
1.  Create users in apache guacamole
2.  Create a group
3.  Assign users to group

Lab creation and assignment
1. In Admin Home Page go to Lab Setup
2. Enter a lab name and vm template id # and submit
3. Go back to Admin Home Page and go into Lab Group Assign
4. Enter group id and lab id and submit.
