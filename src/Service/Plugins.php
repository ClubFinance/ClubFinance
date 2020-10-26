<?php

namespace App\Service;

class Plugins {
    public function get() {
        $plugins = array();

        $plugins[] = array(
            "name" => "Demo-Plugin",
            "route_name" => "demo_plugin",
        );

        return $plugins;
    }
}