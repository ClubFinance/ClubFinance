<?php

namespace App\Service;

class Plugins {
    public function get() {
        $plugins = array();

        // Plugin Status
        $plugins['status'] = false;

        // array für Plugins
        $plugins['plugins'][] = array(
            "name" => "Demo-Plugin",
            "route_name" => "plugin-demo",
        );

        return $plugins;
    }
}