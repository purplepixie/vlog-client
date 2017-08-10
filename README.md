# vlog-client

Install:

````
cd /opt
git clone https://github.com/purplepixie/vlog-client.git
cd vlog-client/client
chmod 755 log
ln -s /opt/vlog-client/client/log /usr/bin/log
````

On one line using sudo:

````
cd /opt && sudo git clone https://github.com/purplepixie/vlog-client.git && cd vlog-client/client && sudo chmod 755 log && sudo ln -s /opt/vlog-client/client/log /usr/bin/log && sudo cp example.config.php config.php
````

Then edit the config file as needed
