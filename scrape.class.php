<?php
class Scrape {

	public $url;
	public $source;
	private $baseUrl;
	private $parsedUrl = array();

	function __construct($url) {
 		$this->url = $url;
	 	$this->source = $this->curlGet($this->url);
 		$this->xPathObj = $this->returnXpathObject($this->source);
	 	$this->parsedUrl = parse_url($this->url);
 		$this->baseUrl = $this->parsedUrl['scheme'] . '://' . $this->parsedUrl['host'];
	}

	public function curlGet($url) {
    	$ch = curl_init();
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($ch, CURLOPT_URL, $url);

    	curl_setopt($ch, CURLOPT_TIMEOUT_MS, 30000);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 5000);

	    $results = curl_exec($ch);

		$redirectURL = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL );
		$qid_1 = explode('/', $redirectURL)[4];
		$qid_2 = explode('/', $url)[4];
		
		curl_close($ch);

		if($qid_1 !== $qid_2) return false;

    	return $results; // Return the results
	}

	public function returnXPathObject($item) {
 		$xmlPageDom = new DomDocument();
	 	@$xmlPageDom->loadHTML($item);
 		$xmlPageXPath = new DOMXPath($xmlPageDom);
	 	return $xmlPageXPath;
	}

}

?>
