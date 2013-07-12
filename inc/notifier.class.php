<?php

if (!defined ('GLPI_ROOT'))
{
     die ("Sorry. You can't access directly to this file");
}

class PluginAmqpNotifier
{
     static function sendAMQPMessage ($msg_body)
     {
          /* get configuration */
          $config = new PluginAmqpConfig ();
          $config->getFromDB (1);

          $cred = array (
               'host'     => $config->getField ('host'),
               'port'     => $config->getField ('port'),
               'login'    => $config->getField ('user'),
               'password' => $config->getField ('password'),
               'vhost'    => $config->getField ('vhost')
          );

          error_log ("Initialize amqp://".$cred['login']."@".$cred['host'].":".$cred['port']."/".$cred['vhost']."...");

          /* connect to AMQP socket */
          $conn = new AMQPConnection ($cred);
          $conn->connect ();
          $channel = new AMQPChannel ($conn);

          /* Declare exchange if not exist */
          error_log ("Declare AMQP exchange ".$config->getField ('exchange')."...");

          $ex = new AMQPExchange ($channel);
          $ex->declare ($config->getField ('exchange'), AMQP_EX_TYPE_FANOUT);

          /* build routing key */
          $msg_rk = $msg_body['connector'].".".$msg_body['connector_name'].".".$msg_body['event_type'].".".$msg_body['source_type'].".".$msg_body['component'];

          if ($msg_body['source_type'] == 'resource')
          {
               $msg_rk .= ".".$msg_body['resource'];
          }

          /* generate AMQP message */
          $msg_raw = json_encode ($msg_body);

          /* publish event */
          $msg = $ex->publish ($msg_raw, $msg_rk);

          if (!$msg)
          {
               error_log ("Error: AMQP message '".$msg."' not sent.");
          }
          else
          {
               error_log ("Success: AMQP message '".$msg."' sent.");
          }

          $conn->disconnect ();
     }

     static function add_item (CommonDBTM $item)
     {
          $event = array (
               "connector"      => "glpi",
               "connector_name" => "glpi2amqp",
               "component"      => "glpi",
               "resource"       => "notifier",
               "source_type"    => "resource",
               "timestamp"      => time (),
               "event_type"     => "log",
               "state"          => 0,
               "output"         => "Add item #".$item->getField ("id"),
               "long_output"    => $item->getField ("content")
          );

          PluginAmqpNotifier::sendAMQPMessage ($event);
     }

     static function update_item (CommonDBTM $item)
     {
     }

     static function delete_item (CommonDBTM $item)
     {
     }

     static function purge_item (CommonDBTM $item)
     {
     }

     static function restore_item (CommonDBTM $item)
     {
     }
}
