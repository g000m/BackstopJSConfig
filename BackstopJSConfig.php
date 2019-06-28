<?php

require __DIR__.'/vendor/autoload.php';

use Alc\SitemapCrawler;

/**
 * @file
 * Contains class BackstopJSConfig.
 *
 * It parses sitemap.xml by given URL, and generates config file for BackstopJS
 * by given attributes.
 */

class BackstopJSConfig{
  private $siteMapXMLURL = '';
  private $defaultScenario = NULL;

  private $viewports = array();
  private $scenarios = array();
  private $paths = array();
  private $engine = '';
  private $report = array();
  private $debug = FALSE;
  private $port = 0;

  private $defaultConfigPath = './defaultConfig.json';

  private $destDir = './config.json';

  /**
   * BackstopJSConfig constructor.
   * @param $siteMapXMLURL
   *   URL of sitemap.xml
   */
  public function __construct($siteMapXMLURL) {
    // Set sitemap.xml URL.
    $this->siteMapXMLURL = $siteMapXMLURL;

    // Load default config presets.
    $defaultConfig = $this->loadDefaultConfig();

    // Load viewports from config template.
    if (!empty($defaultConfig['viewports']) && is_array($defaultConfig['viewports'])) {
      foreach ($defaultConfig['viewports'] as $viewport) {
        $this->viewports[] = (object) $viewport;
      }
    }

    // Load default scenario from config template. That will be used for
    // generating scenarios from sitemap.xml.
    if (!empty($defaultConfig['defaultScenario'])) {
      $this->defaultScenario = (object) $defaultConfig['defaultScenario'];
    }

    // Load paths from config template.
    if (!empty($defaultConfig['paths']) && is_array($defaultConfig['paths'])) {
      foreach ($defaultConfig['paths'] as $path) {
        $this->paths[] = $path;
      }
    }

    // Load engine from config template.
    if (!empty($defaultConfig['engine'])) {
      $this->engine = $defaultConfig['engine'];
    }

    // Load report from config template.
    if (!empty($defaultConfig['report']) && is_array($defaultConfig['report'])) {
      foreach ($defaultConfig['report'] as $report) {
        $this->report[] = $report;
      }
    }

    // Load debug from config template.
    if (!empty($defaultConfig['debug'])) {
      $this->debug = $defaultConfig['debug'];
    }

    // Load port from config template.
    if (!empty($defaultConfig['port'])) {
      $this->port = $defaultConfig['port'];
    }
  }

  /**
   * Load default config template.
   * @return mixed
   */
  private function loadDefaultConfig() {
    $string = file_get_contents($this->defaultConfigPath);
    return json_decode($string, TRUE);
  }


  /**
   * Load scenarios from sitemap.xml.
   */
	private function loadScenarios() {
		$crawler = new SitemapCrawler();

		$sitemap = $crawler->crawl( $this->siteMapXMLURL );

		foreach ( $sitemap as $item ) {
			$page_url = (string) $item->getUrl();

			// Getting "title" for the scenario.
			$parsed_url  = parse_url( $page_url );
			$parsed_path = $parsed_url['path'];
			$parsed_path = explode( '/', $parsed_path );
			$parsed_path = array_values( array_filter( $parsed_path ) );

			// Get URL title from its path.
			if ( count( $parsed_path ) > 0 ) {
				$parsed_title = $parsed_path[ count( $parsed_path ) - 1 ];
				$parsed_title = urldecode( $parsed_title );
				$parsed_title = str_replace( array( '-', '_' ), ' ', $parsed_title );
				$page_title   = ucwords( $parsed_title );
			} else {
				$page_title = "Home page";
			}

			// Creating scenario based on item from site map.
			$scenario          = $this->getDefaultScenario();
			$scenario->label   = $page_title;
			$scenario->url     = $page_url;
			$this->scenarios[] = $scenario;
		}
	}

  /**
   * Generate JSON config file based on loaded input.
   * @param string $destPath
   */
  public function generateConfig($destPath = '') {
    if (empty($destPath)) {
      $destPath = $this->destDir;
    }

    $this->loadScenarios();

    $configOutput = new stdClass();
    $configOutput->viewports = $this->viewports;
    $configOutput->scenarios = $this->scenarios;
    $configOutput->paths = $this->paths;
    $configOutput->engine = $this->engine;
    $configOutput->report = $this->report;
    $configOutput->debug = $this->debug;

    $fp = fopen($destPath, 'w');
    fwrite($fp, json_encode($configOutput, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
    fclose($fp);
  }

  /**
   * Getter for default scenario.
   * @return null|object
   */
  public function getDefaultScenario() {
    $scenario = new stdClass();
    $vars = get_object_vars($this->defaultScenario);
    foreach ($vars as $name => $value) {
      $scenario->$name = $value;
    }

    return $scenario;
  }

  /**
   * @param string $siteMapXMLURL
   */
  public function setSiteMapXMLURL($siteMapXMLURL) {
    $this->siteMapXMLURL = $siteMapXMLURL;
  }

  /**
   * @param array $viewports
   */
  public function setViewports($viewports) {
    $this->viewports = $viewports;
  }

  /**
   * @param array $scenarios
   */
  public function setScenarios($scenarios) {
    $this->scenarios = $scenarios;
  }

  /**
   * @param array $paths
   */
  public function setPaths($paths) {
    $this->paths = $paths;
  }

  /**
   * @param string $engine
   */
  public function setEngine($engine) {
    $this->engine = $engine;
  }

  /**
   * @param array $report
   */
  public function setReport($report) {
    $this->report = $report;
  }

  /**
   * @param boolean $debug
   */
  public function setDebug($debug) {
    $this->debug = $debug;
  }

  /**
   * @param int $port
   */
  public function setPort($port) {
    $this->port = $port;
  }
}
