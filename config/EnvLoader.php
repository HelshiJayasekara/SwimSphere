<?php
/**
 * EnvLoader
 * 
 * A simple class to load environment variables from an .ini file.
 * This ensures sensitive configuration is separated from the source code.
 */
class EnvLoader {
    /**
     * The directory where the .ini file is located.
     * @var string
     */
    protected $path;

    public function __construct(string $path) {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('The config file does not exist at %s', $path));
        }
        $this->path = $path;
    }

    /**
     * Parse the .ini file and load variables into the environment.
     */
    public function load(): void {
        if (!is_readable($this->path)) {
            throw new \RuntimeException(sprintf('The config file is not readable at %s', $this->path));
        }

        // Parse the INI file natively
        $variables = parse_ini_file($this->path, false);
        
        if ($variables === false) {
            throw new \RuntimeException(sprintf('Failed to parse the config file at %s', $this->path));
        }

        foreach ($variables as $name => $value) {
            // Load into environment arrays
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}
