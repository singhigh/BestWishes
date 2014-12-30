<?php

namespace BestWishes\Config;


use Symfony\Component\Yaml\Yaml;

class ConfigLoader {
    /**
     * @var string
     */
    private $configPath;

    public function __construct($configPath) {
        $this->configPath = $configPath;
    }

    public function getDatabaseConfig() {
        $yamlConfig = Yaml::parse($this->configPath . '/database.yml');
        return $yamlConfig;
    }
}