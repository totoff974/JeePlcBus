#!/bin/bash

touch /tmp/JeePlcBus_dep
echo "Début de l'installation"
echo 0 > /tmp/JeePlcBus_dep

echo "Dépendance pour Perl"
echo 30 > /tmp/JeePlcBus_dep
sudo apt-get install -y libdevice-serialport-perl 

echo "Dépendance installée pour Perl"

echo "Vérification existance du dossier SerialLibs"
echo 60 > /tmp/JeePlcBus_dep
if [ ! -d "/etc/perl/SerialLibs" ]
	then
		sudo mkdir /etc/perl/SerialLibs
		echo "Création du dossier SerialLibs - ok"	
fi

echo "Installation de IOSelectBuffered.pm"
echo 90 > /tmp/JeePlcBus_dep
echo "Copie de IOSelectBuffered.pm - ok"

echo "Fin de l'installation"
rm /tmp/JeePlcBus_dep
