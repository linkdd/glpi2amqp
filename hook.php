<?php

function plugin_amqp_install ()
{
     global $DB;

     if (!TableExists ('glpi_plugin_amqp_configs'))
     {
          $query = "CREATE TABLE `glpi_plugin_amqp_configs` (
               `id` INT(11) NOT NULL auto_increment,
               `host` VARCHAR(255) NOT NULL,
               `port` INT(11) NOT NULL,
               `user` VARCHAR(255) NOT NULL,
               `password` VARCHAR(255) NOT NULL,
               `vhost` VARCHAR(255) NOT NULL,
               `exchange` VARCHAR(255) NOT NULL,
               `cron_interval` INT(11) NOT NULL,
               PRIMARY KEY (`id`)
          ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

          $DB->query ($query) or die ('Error while creating configuration table: '.$DB->error ());

          $query = "INSERT INTO `glpi_plugin_amqp_configs`
               (`id`, `host`, `port`, `user`, `password`, `vhost`, `exchange`, `cron_interval`)
          VALUES (
               NULL,
               '127.0.0.1',
               5672,
               'guest',
               'guest',
               'myvhost',
               'myvhost.events',
               3600
          )";

          $DB->query ($query) or die ('Error while creating default configuration: '.$DB->error ());
     }

     return true;
}

function plugin_amqp_uninstall ()
{
     global $DB;

     if (TableExists ('glpi_plugin_amqp_configs'))
     {
          $query = "DROP TABLE `glpi_plugin_amqp_configs`";
          $DB->query ($query) or die ('Error while cleaning configuration table: '.$DB->error ());
     }

     return true;
}

function plugin_pre_item_add_amqp ($item)
{
     if ($item instanceof Ticket)
     {
          return PluginAmqpNotifier::add_item ($item);
     }
}

function plugin_pre_item_update_amqp ($item)
{
     if ($item instanceof Ticket)
     {
          return PluginAmqpNotifier::update_item ($item);
     }
}

function plugin_pre_item_delete_amqp ($item)
{
     if ($item instanceof Ticket)
     {
          return PluginAmqpNotifier::delete_item ($item);
     }
}

function plugin_pre_item_purge_amqp ($item)
{
     if ($item instanceof Ticket)
     {
          return PluginAmqpNotifier::purge_item ($item);
     }
}

function plugin_pre_item_restore_amqp ($item)
{
     if ($item instanceof Ticket)
     {
          return PluginAmqpNotifier::restore_item ($item);
     }
}
