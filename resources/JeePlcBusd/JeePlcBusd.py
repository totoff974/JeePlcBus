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
			0x00 : 'ALL_UNITS_OFF',
			0x01 : 'ALL_LIGHTS_ON',
			0x02 : 'ON',
			0x03 : 'OFF',
			0x04 : 'DIM',
			0x05 : 'BRIGHT',
			0x06 : 'ALL_LIGHTS_OFF',
			0x07 : 'ALL_USER_LIGHTS_ON',
			0x08 : 'ALL_USER_UNITS_OFF',
			0x09 : 'ALL_USER_LIGHTS_OFF',
			0x0a : 'BLINK',
			0x0b : 'FADE_STOP',
			0x0c : 'PRESET_DIM',
			#0x0d : 'STATUS_ON',
			#0x0e : 'STATUS_OFF',
			0x0f : 'STATUS_REQUEST',
			0x10 : 'RX_MASTER_ADDR_SETUP',
			0x11 : 'TX_MASTER_ADDR_SETUP',
			0x12 : 'SCENE_ADDR_SETUP',
			0x13 : 'SCENE_ADDR_ERASE',
			0x14 : 'ALL_SCENES_ADDR_ERASE',
			0x18 : 'GET_SIGNAL_STRENGTH',
			0x19 : 'GET_NOISE_STRENGTH',
			#0x1a : 'REPORT_SIGNAL_STRENGTH',
			#0x1b : 'REPORT_NOISE_STRENGTH',
			0x1c : 'GET_ALL_ID_PULSE',
			0x1d : 'GET_ON_ID_PULSE'
			}
			
# ----------------------------------------------------------------------------

def read_socket():
	try:
		global JEEDOM_SOCKET_MESSAGE
		if not JEEDOM_SOCKET_MESSAGE.empty():
			logging.debug("Message received in socket JEEDOM_SOCKET_MESSAGE")
			message = json.loads(jeedom_utils.stripped(JEEDOM_SOCKET_MESSAGE.get()))
			if message['apikey'] != _apikey:
				logging.error("Invalid apikey from socket : " + str(message))
				return
			if message['apikey'] != _apikey:
					logging.error("Invalid apikey from socket : " + str(message))
					return
			elif message['cmd'] == 'send':
				if isinstance(message['data'], list):
					for data in message['data']:
						try:
							send_plcbus(data)
						except Exception, e:
							logging.error('Send command to PlcBus error : '+str(e))
				else:
					try:
						send_plcbus(message['data'])
					except Exception, e:
						logging.error('Send command to PlcBus error : '+str(e))
	except Exception,e:
		logging.error('Error on read socket : '+str(e))

# ----------------------------------------------------------------------------	

def send_plcbus(message):
	message = test_PlcBus(message)
	jeedom_serial.flushOutput()
	jeedom_serial.flushInput()
	logging.debug("Write message to serial port")
	logging.debug("WRITE : " + message)
	jeedom_serial.write(message.decode('hex') )
	logging.debug("Write message ok : "+ jeedom_utils.ByteToHex(message.decode('hex')))
	try:
		logging.debug("Decode message")
	except Exception, e:
		logging.error('Unrecognizable packet : '+str(e))

# ----------------------------------------------------------------------------

def test_PlcBus(message):
	logging.debug("Test message: " + message)
	message = plcbus_tx_command(message)
	message = jeedom_utils.stripped(message)
	logging.debug("STRIPPED : " + message)
	try:
		message = message.replace(' ', '')
	except Exception, e:
		logging.debug("Error: Removing white spaces")
		return False
	try:
		int(message,16)
	except Exception, e:
		logging.debug("Error: Packet not hex format")
		return False
	if len(message) % 2:
		logging.debug("Error: Packet length not even")
		return False
	if jeedom_utils.ByteToHex(message.decode('hex')[0]) == "00":
		logging.debug("Error: Packet first byte is 00")
		return False
	if not len(message.decode('hex')) > 1:
		logging.debug("Error: Packet is not longer than one byte")
		return False
	cmd_len = int( jeedom_utils.ByteToHex( message.decode('hex')[2]),16 )
	if not len(message.decode('hex')) == 8:
		logging.debug("Error: Packet length is not valid : " + str(len(message.decode('hex'))))
		logging.debug("Error: Packet length is not valid : " + str((cmd_len + 3)))
		return False
	return message

########################
## transmission des trames
########################
def plcbus_tx_command(message):
	params = message.upper()
	logging.debug("Params: " + params)

	params_data = params.split("::")
	
	homeunit = params_data[0]
	home = params_data[0][:1]
	unit = params_data[0][1:]
	
	home = (jeedom_utils.StrToHex(home) - 65) * 16
	unit = int(unit) - 1
	homeunit = jeedom_utils.dec2hex(home + unit)[:-1].upper()
		
	command = hex(plcbus_command_to_hex[params_data[1]])[2:]
	command = int(command, 16)

	if ((command >= 28) or (command <= 1) or ((command >= 6) and (command <=9))):
		homeunit = long(homeunit,16) & 240
		homeunit = jeedom_utils.dec2hex(homeunit)[:-1].upper()
		
	if ((command == 28) or (command == 29)):
		command += 32
		
	if (_phase == 3):
		command += 64
		
	if (len(homeunit)<2):
		homeunit = "0"+homeunit
	else:
		homeunit = str(homeunit)
	
	command = hex(command)[2:].upper()
	if (len(str(command))<2):
		command = "0"+str(command)
	else:
		command = str(command)

	try:
		params_data[2]
	except NameError:
		plcbus_data1 = "00"
	else:
		plcbus_data1 = int(str(params_data[2]),16)
		plcbus_data1 = str(hex(plcbus_data1)[2:].upper())

	try:
		params_data[3]
	except NameError:
		plcbus_data2 = "00"
	else:
		plcbus_data2 = int(str(params_data[3]),16)
		plcbus_data2 = str(hex(plcbus_data2)[2:].upper())

	if (len(plcbus_data1)<2):
		plcbus_data1 = "0"+plcbus_data1
	else:
		plcbus_data1 = str(plcbus_data1)
		
	if (len(plcbus_data2)<2):
		plcbus_data2 = "0"+plcbus_data2
	else:
		plcbus_data2 = str(plcbus_data2)
		
	plcbus_frame = "02" + " " + "05" + " " + _usercode[2:] + " " + homeunit + " " + command + " " + plcbus_data1 + " " + plcbus_data2 + " " + "03"
	logging.debug("FRAME : " + str(plcbus_frame))
	return plcbus_frame
	
########################
## reception des trames
########################
def plcbus_rx_status(homeunit, action, message):

	status = jeedom_utils.StrToHex(message[4]) & 0x1F
	if (action != None):
		action = action & 0x1F
	
	# Si la reponse est une reponse directe de la commande emise
	if ((homeunit != None) and (homeunit == jeedom_utils.StrToHex(message[3]))):
		
		# Si trois phases, attendre le rapport etat du coupleur
		if (_phase==3):
			if ((jeedom_utils.StrToHex(message[4]) == 0x0D) or (jeedom_utils.StrToHex(message[4]) == 0x0E) or (jeedom_utils.StrToHex(message[4]) >= 0x1E)):
				return True
		
		# Si une commande de requete identification ID attend un signal de retour ID rxed
		elif ((action == 0x1C) or (action == 0x1D)):
			if (jeedom_utils.StrToHex(message[7]) == 0x40):
				return True
		
		# Si une commande de requete (request or get) attend un rapport de succes PLCBUS autre unite
		
		elif ((action == 0x0F) or (action == 0x18) or (action == 0x19)):
			if (jeedom_utils.StrToHex(message[7]) == 0x0C):
				return True
		
		# Si une configuration ou effacement de scene ou une activation ou desactivation adresse de scene Unite 10 a 16 pour chaque Home envoyer un 1.
		
		elif ((action == 0x12) or (action == 0x13) or (action == 0x14) or (((action == 0x02) or (action == 0x03)) and ((jeedom_utils.StrToHex(message[3]) & 0x0F) >= 0x09))):
			if (jeedom_utils.StrToHex(message[7]) == 0x1C):
				return True
		
		# Toutes les autres commandes requierent seulement un ACK
		elif (jeedom_utils.StrToHex(message[7]) == 0x20):
			return True
			
	elif ((status == 0x0D) or (status == 0x0E) or ((homeunit != None) and (homeunit != jeedom_utils.StrToHex(message[3])))):
		logging.debug("Detection du trafic initie par un autre emetteur")
		plcbus_output_message(message)
		return False
		
	elif (homeunit == None):
		logging.debug("Detection du trafic initie par un autre emetteur")
		return True
		
	else:
		return False

########################
## validation des trames
########################
def plcbus_rx_valid_frame(message):
	# Did we receive a valid 9 byte PLCBUS frame?
	if (len(message) == 9):
		logging.debug("Packet Received = " + jeedom_utils.ByteToHex(message))
		
		# STX is Frame Start Bit 02H, LENGTH is DATA length, DATA is Data Bit, and ETX is Frame End Bit 03H
		if (((jeedom_utils.StrToHex(message[1]) == 0x06) and (jeedom_utils.StrToHex(message[0]) == 0x02) and (jeedom_utils.StrToHex(message[8]) == 0x03)) or ((sum(bytearray(message)) % 0x100) == 0x0)):
			logging.debug("Valid Packet Received")
			return True
		else:
			logging.debug("Invalid Packet Received")
			return False

########################
## OUTPUT Message
########################
def plcbus_output_message(message):
	test = jeedom_utils.StrToHex(message[4]) & 0x1F
	plcbus_status = plcbus_hex_to_command[test]
	home = int(jeedom_utils.StrToHex(message[3]) / 16) + 65
	home = struct.pack('l', home)
	unit = jeedom_utils.StrToHex(message[3]) % 16 + 1
	
	action = {}
	action['id'] = str(home)[:1] + str(unit)
	action['housecode'] = str(home)[:1]
	action['unitcode'] = str(unit)
	action['command'] = str(plcbus_status)
	action['data1'] = str(jeedom_utils.StrToHex(message[5]))
	action['data2'] = str(jeedom_utils.StrToHex(message[6]))
	
	plcbus_status = str(action['id']+","+action['command']+","+action['data1']+","+action['data2'])
	logging.debug('Decode data : '+str(action))
	try:
		globals.JEEDOM_COM.add_changes('devices::'+action['id'],action)
	except Exception, e:
		pass
	
	return plcbus_status

# ----------------------------------------------------------------------------
	
def read_plcbus():
	message = None
	try:
		byte = jeedom_serial.read()
	except Exception, e:
		logging.error("Error in read_plcbus: " + str(e))
		if str(e) == '[Errno 5] Input/output error':
			logging.error("Exit 1 because this exeption is fatal")
			shutdown()
	try:
		if byte:
			message = byte + jeedom_serial.readbytes(8)
			if plcbus_rx_valid_frame(message):
				logging.debug("OUTPUT : " + str(plcbus_output_message(message)))

			else:
				logging.error("Error: Incoming packet not valid length (" + jeedom_utils.ByteToHex(message) + ")."  + str(len(message)))
	except OSError, e:
		logging.error("Error in read_plcbus on decode message : " + str(jeedom_utils.ByteToHex(message))+" => "+str(e))

# ----------------------------------------------------------------------------

def listen():
	logging.debug("Start listening...")
	jeedom_serial.open()
	jeedom_socket.open()
	jeedom_serial.flushOutput()
	jeedom_serial.flushInput()
	logging.debug("Start deamon")
	try:
		while 1:
			time.sleep(0.02)
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
