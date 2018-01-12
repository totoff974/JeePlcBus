# This file is part of Jeedom.
#
# Jeedom is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# Jeedom is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with Jeedom. If not, see <http://www.gnu.org/licenses/>.

import globals
import serial
import struct
import logging
import string
import sys
import os
import time
import argparse
import datetime
import binascii
import re
import signal
import traceback
import xml.dom.minidom as minidom
from optparse import OptionParser
from os.path import join
import json

try:
	from jeedom.jeedom import *
except ImportError:
	print "Error: importing module jeedom.jeedom"
	sys.exit(1)
	
plcbus_command_to_hex = {
			'ALL_UNITS_OFF' : 0x00,
			'ALL_LIGHTS_ON' : 0x01,
			'ON' : 0x02,
			'OFF' : 0x03,
			'DIM' : 0x04,
			'BRIGHT' : 0x05,
			'ALL_LIGHTS_OFF' : 0x06,
			'ALL_USER_LIGHTS_ON' : 0x07,
			'ALL_USER_UNITS_OFF' : 0x08,
			'ALL_USER_LIGHTS_OFF' : 0x09,
			'BLINK' : 0x0a,
			'FADE_STOP' : 0x0b,
			'PRESET_DIM' : 0x0C,
			#'STATUS_ON' : 0x0d,
			#'STATUS_OFF' : 0x0e,
			'STATUS_REQUEST' : 0x0f,
			'RX_MASTER_ADDR_SETUP' : 0x10,
			'TX_MASTER_ADDR_SETUP' : 0x11,
			'SCENE_ADDR_SETUP' : 0x12,
			'SCENE_ADDR_ERASE' : 0x13,
			'ALL_SCENES_ADDR_ERASE' : 0x14,
			'GET_SIGNAL_STRENGTH' : 0x18,
			'GET_NOISE_STRENGTH' : 0x19,
			#	'REPORT_SIGNAL_STRENGTH' : 0x1a,
			#	'REPORT_NOISE_STRENGTH' : 0x1b,
			'GET_ALL_ID_PULSE' : 0x1c,
			'GET_ON_ID_PULSE' : 0x1d
			}

plcbus_hex_to_command = {
			'0x0' : 'ALL_UNITS_OFF',
			'0x1' : 'ALL_LIGHTS_ON',
			'0x2' : 'ON',
			'0x3' : 'OFF',
			'0x4' : 'DIM',
			'0x5' : 'BRIGHT',
			'0x6' : 'ALL_LIGHTS_OFF',
			'0x7' : 'ALL_USER_LIGHTS_ON',
			'0x8' : 'ALL_USER_UNITS_OFF',
			'0x9' : 'ALL_USER_LIGHTS_OFF',
			'0xa' : 'BLINK',
			'0xb' : 'FADE_STOP',
			'0xc' : 'PRESET_DIM',
			'0xd' : 'STATUS_ON',
			'0xe' : 'STATUS_OFF',
			'0xf' : 'STATUS_REQUEST',
			'0x10' : 'RX_MASTER_ADDR_SETUP',
			'0x11' : 'TX_MASTER_ADDR_SETUP',
			'0x12' : 'SCENE_ADDR_SETUP',
			'0x13' : 'SCENE_ADDR_ERASE',
			'0x14' : 'ALL_SCENES_ADDR_ERASE',
			'0x18' : 'GET_SIGNAL_STRENGTH',
			'0x19' : 'GET_NOISE_STRENGTH',
			'0x1a' : 'REPORT_SIGNAL_STRENGTH',
			'0x1b' : 'REPORT_NOISE_STRENGTH',
			'0x1c' : 'GET_ALL_ID_PULSE',
			'0x1d' : 'GET_ON_ID_PULSE'
			}
			

# ----------------------------------------------------------------------------
# ----------------------------------------------------------------------------
# ----------------------------------------------------------------------------

###########################
## lecture des trames recues
###########################
def MiseEnFormeReceive(trame_rx, format):
	trame = []
	
	for i in range(0, len(trame_rx), 2):
		if (format == "str"):
			trame.append(trame_rx[i:i+2])
		elif (format == "int"):
			trame.append(int(trame_rx[i:i+2],16))
		else:
			trame = []
			
	return trame

def Valid_RX_Receive(trame_rx):
	trame_int = MiseEnFormeReceive(trame_rx, 'int')
	
	checksum = sum(trame_int)
	
	if (((trame_int[0] == 2) and (trame_int[1] == 6) and (trame_int[8] == 3)) or (checksum == 512)):
		return True
	else:
		return False
		
def Statut_RX_Receive(homeunit, action, trame_rx):
	trame_int = MiseEnFormeReceive(trame_rx, 'int')
	statut = trame_int[4] & 31
	
	if (homeunit and homeunit == trame_int[3]):
		# si 3 phases
		if (_phase == 3):
			if (trame[4] == 13 or trame[4] == 14 or trame[4] >= 30):
				return True
		
		elif (action == 28 or action == 29):
			if (trame_int[7] == 12):
				return True
		
		elif (action == 18 or action == 19 or action == 20 or ((action == 2 or action == 3) and ((trame_int[3] & 15) >= 9))):
			if (trame_int[7] == 28):
				return True
				
		elif (trame_int[7] == 32):
			return True
	
	elif (statut == 13 or statut == 14 or (homeunit and homeunit != trame_int[3])):
		logging.debug("Trafic controleur")
		logging.debug(plcbus_rx_out(trame_rx))
		return True
		
	elif not homeunit:
		logging.debug("Trafic autre controleur")
		logging.debug(plcbus_rx_out(trame_rx))
		return True
		
	else:
		return False

def plcbus_rx_out(trame_rx):
	trame_int = MiseEnFormeReceive(trame_rx, 'int')
	
	# pour la commande
	commande = trame_int[4] & 31
	plcbus_cmd = plcbus_hex_to_command[hex(commande)]

	# pour le homeunit
	home = (trame_int[3] / 16) + 65
	home = struct.pack('l', home)
	unit = trame_int[3] %16 + 1
	homeunit = str(home) + str(unit)
	
	# pour les data1 et data2
	data1 = str(trame_int[5])
	data2 = str(trame_int[6])
	
	plcbus_receive = homeunit + "::" + plcbus_cmd + "::" + data1 + "::" + data2
	return plcbus_receive
	
def affiche_rx(homeunit, action, trame_rx):
	if Valid_RX_Receive(trame_rx):
		if Statut_RX_Receive(homeunit, action, trame_rx):
			send_socket(plcbus_rx_out(trame_rx))
	
###########################
## Mise en forme des trames a envoyer
###########################

def MiseEnFormeCommande(CommandeTexte, ack):
	# commande_texte -> exemple -> A1::ON::0:0
	params = str(CommandeTexte)
	params_data = params.split("::")

	### traitement du homeunit
	homeunit = params_data[0]
	home = params_data[0][:1]
	unit = params_data[0][1:]
	
	home = (int(home.encode("hex"), 16) - 65) * 16
	unit = int(unit) - 1
	homeunit = (hex(home + unit)[2:].upper()).zfill(2) # en str mise en forme 2 digits
	
	### traitement de la commande
	command = int(plcbus_command_to_hex[params_data[1]]) # en decimale
	
	# bit-5 a 1 pour ACK_pulse
	if (command != 28 or command != 29) and ack != "0":
		command += 32
		
	# bit-6 a 1 si 3 phases
	if _phase == 3:
		command += 64
		
	command = (hex(command)[2:].upper()).zfill(2) # en str mise en forme 2 digits
	
	## traitement plcbus_data1 et plcbus_data2
	
	try:
		plcbus_data1 = params_data[2]
	except:
		plcbus_data1 = "00"	
	
	try:
		plcbus_data2 = params_data[3]
	except:
		plcbus_data2 = "00"	
	
	if ((int(plcbus_data1) < 0) or (int(plcbus_data1) > 100)):
		logging.debug("ERREUR, Data1 Value")
		return 0

	if ((int(plcbus_data2) < 0) or (int(plcbus_data2) > 100)):
		logging.debug("ERREUR, Data2 Value")
		return 0
	
	plcbus_data1 = (hex(int(plcbus_data1))[2:].upper()).zfill(2) # en str mise en forme 2 digits
	plcbus_data2 = (hex(int(plcbus_data2))[2:].upper()).zfill(2) # en str mise en forme 2 digits
	
	## frame a envoyer
	plcbus_frame = "02" + "05" + _usercode + homeunit + command + plcbus_data1 + plcbus_data2 + "03"
	return plcbus_frame

# ----------------------------------------------------------------------------
# ----------------------------------------------------------------------------
# ----------------------------------------------------------------------------



def read_socket():
	try:
		global JEEDOM_SOCKET_MESSAGE
		if not JEEDOM_SOCKET_MESSAGE.empty():
			message = json.loads(jeedom_utils.stripped(JEEDOM_SOCKET_MESSAGE.get()))
			if message['apikey'] != _apikey:
				logging.error("Invalid apikey from socket : " + str(message))
				return
			if message['apikey'] != _apikey:
					logging.error("Invalid apikey from socket : " + str(message))
					return
			elif message['cmd'] == 'send':
				ack = message['ack']
				logging.debug("ACK => " + ack)
				if isinstance(message['data'], list):
					for data in message['data']:
						try:
							send_plcbus(data, ack)
						except Exception, e:
							logging.error('Send command to PlcBus error : '+str(e))
	except Exception,e:
		logging.error('Error on read socket : '+str(e))

# ----------------------------------------------------------------------------	

def send_plcbus(plcbus_frame, ack):
	jeedom_serial.flushOutput()
	jeedom_serial.flushInput()
	plcbus_frame = MiseEnFormeCommande(plcbus_frame, ack)
	logging.debug("Write Serial Port : " + plcbus_frame)
	jeedom_serial.write(plcbus_frame.decode('hex'))
	time.sleep(3)
	logging.debug("Write message Ok  : " + plcbus_frame)

# ----------------------------------------------------------------------------


########################
## OUTPUT Message
########################
def send_socket(message):
	message = message.split("::")

	action = {}
	action['id'] = str(message[0]).replace('\x00', '')
	action['command'] = str(message[1]).replace('\x00', '')
	action['data1'] = str(message[2]).replace('\x00', '')
	action['data2'] = str(message[3]).replace('\x00', '')
	
	plcbus_statut = str(action['id']+","+action['command']+","+action['data1']+","+action['data2'])
	logging.debug('Decode data : '+str(action))
	try:
		globals.JEEDOM_COM.add_changes('devices::'+action['id'],action)
	except Exception, e:
		pass
	
	return plcbus_statut

# ----------------------------------------------------------------------------
	
def read_plcbus():
	message = None
	try:
		byte = jeedom_serial.read()
		if byte:
			if byte.encode('hex') == '02':
				message = byte + jeedom_serial.readbytes(8)
				message = str(message.encode('hex'))
				logging.debug("Serial IN message brut : " + message)
				affiche_rx(None, None, message)
				
	except Exception, e:
		logging.error("Error in read_plcbus: " + str(e))
		if str(e) == '[Errno 5] Input/output error':
			logging.error("Exit 1 because this exeption is fatal")
			shutdown()


def listen():
	logging.debug("Start listening...")
	jeedom_serial.open()
	jeedom_socket.open()
	jeedom_serial.flushOutput()
	jeedom_serial.flushInput()
	logging.debug("Start deamon")
	try:
		while 1:
			time.sleep(0.03)
			read_plcbus()
			read_socket()
	except KeyboardInterrupt:
		shutdown()

# ----------------------------------------------------------------------------

def handler(signum=None, frame=None):
	logging.debug("Signal %i caught, exiting..." % int(signum))
	shutdown()

def shutdown():
	logging.debug("Shutdown")
	logging.debug("Removing PID file " + str(_pidfile))
	try:
		os.remove(_pidfile)
	except:
		pass
	try:
		jeedom_socket.close()
	except:
		pass
	try:
		jeedom_serial.close()
	except:
		pass
	logging.debug("Exit 0")
	sys.stdout.flush()
	os._exit(0)

# ----------------------------------------------------------------------------

_log_level = "error"
_socket_port = 55250
_socket_host = '127.0.0.1'
_device = 'auto'
_pidfile = '/tmp/JeePlcBusd.pid'
_apikey = ''
_callback = ''
_serial_rate = 9600
_serial_timeout = 200
_cycle = 0.3
_usercode = '0xFF'
_phase = 1

parser = argparse.ArgumentParser(description='PLCBus Daemon for Jeedom plugin')
parser.add_argument("--device", help="Device", type=str)
parser.add_argument("--socketport", help="Socketport for server", type=str)
parser.add_argument("--loglevel", help="Log Level for the daemon", type=str)
parser.add_argument("--callback", help="Callback", type=str)
parser.add_argument("--apikey", help="Apikey", type=str)
parser.add_argument("--cycle", help="Cycle to send event", type=str)
parser.add_argument("--serialrate", help="Device serial rate", type=str)
parser.add_argument("--pid", help="Pid file", type=str)
parser.add_argument("--usercode", help="Usercode for module", type=str)
parser.add_argument("--phase", help="Phase number", type=int)
args = parser.parse_args()

if args.device:
	_device = args.device
if args.usercode:
	_usercode = args.usercode
if args.phase:
	_phase = args.phase
if args.socketport:
	_socket_port = int(args.socketport)
if args.loglevel:
	_log_level = args.loglevel
if args.callback:
	_callback = args.callback
if args.apikey:
	_apikey = args.apikey
if args.pid:
	_pidfile = args.pid
if args.serialrate:
	_serial_rate = int(args.serialrate)
if args.cycle:
	_cycle = float(args.cycle)

if _usercode[:2] == '0x':
	_usercode = _usercode[2:]
	
jeedom_utils.set_log_level(_log_level)

logging.info('Start JeePlcBusd')
logging.info('Log level : '+str(_log_level))
logging.info('Socket port : '+str(_socket_port))
logging.info('Socket host : '+str(_socket_host))
logging.info('PID file : '+str(_pidfile))
logging.info('Device : '+str(_device))
logging.info('UserCode : '+str(_usercode))
logging.info('Phase number : '+str(_phase))
logging.info('Apikey : '+str(_apikey))
logging.info('Callback : '+str(_callback))
logging.info('Cycle : '+str(_cycle))
logging.info('Serial rate : '+str(_serial_rate))
logging.info('Serial timeout : '+str(_serial_timeout))

if _device == 'auto':
	_device = jeedom_utils.find_tty_usb('067b','2303')
	logging.info('Find device : '+str(_device))

if _device is None:
	logging.error('No device found')
	shutdown()	

signal.signal(signal.SIGINT, handler)
signal.signal(signal.SIGTERM, handler)	

try:
	jeedom_utils.write_pid(str(_pidfile))
	globals.JEEDOM_COM = jeedom_com(apikey = _apikey,url = _callback,cycle=_cycle)
	if not globals.JEEDOM_COM.test():
		logging.error('Network communication issues. Please fixe your Jeedom network configuration.')
		shutdown()
	jeedom_serial = jeedom_serial(device=_device,rate=_serial_rate,timeout=_serial_timeout,parity = 'N',stopbits = 1,bytesize = 8)
	jeedom_socket = jeedom_socket(port=_socket_port,address=_socket_host)
	listen()
except Exception, e:
	logging.error('Fatal error : '+str(e))
	logging.debug(traceback.format_exc())
	shutdown()
