<?php
/**
 * sitemap Creator
 *
 * PHP versions 5
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   class
 * @package    sitemap
 * @author     Mhd Zaher Ghaibeh <zaher@mhdzaherghaibeh.name>
 * @copyright  2009 Mhd Zaher Ghaibeh
 * @license    http://www.gnu.org/licenses/gpl.html  GPL V 2.0
 * @version    CVS: $Id: sitemap.php,v 0.8 2009/01/24 cellog Exp $
 */

class sitemap {
	function __constructor(){
		private $file_name= 'sitemap.xml';
		public $siteUrl = '';
		public $proxy = NULL;
		public $proxy_port=NULL;	
	}
	public function prepare($siteUrl){
		$this->siteUrl = $siteUrl;
		require_once(ABSPath.'/lib/php/pear/System.php');
		if(!file_exists(ABSPath.'/'.$this->file_name)){
			$handle = fopen(ABSPath.'/'.$this->file_name,'w');
			fwrite($handle,$this->writeFirst());
		}else{
			@System::rm('-r '.ABSPath.'/'.$this->file_name);
			$handle = fopen(ABSPath.'/'.$this->file_name,'w');
			fwrite($handle,$this->writeFirst());
		}
		fclose($handle);
		return true;
	}

	private function writeFirst(){
		$this->defaultData = <<<XML
<?xml version='1.0' encoding='UTF-8'?>
<!-- sitemap-generator-program="sitemap-creator-class" sitemap-generator-version="0.8" -->
<!-- programmed-by="Mhd Zaher Ghaibeh" programmer-email="zaher@mhdzaherghaibeh.name" -->
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
</urlset>
XML;
		return $this->defaultData;
	}

	public function addElements($elementArray){
		$xml = new SimpleXMLElement($this->writeFirst());
		$count = count($elementArray);
		for($i=0;$i<$count;$i++){
			$data = $xml->addChild('url');
			$data->addChild('loc',htmlentities($elementArray[$i]['loc']));
			$data->addChild('lastmod', $elementArray[$i]['lastmod']);
			$data->addChild('changefreq',$elementArray[$i]['changefreq']);
			$data->addChild('priority',$elementArray[$i]['priority']);
		}
		$this->write($xml->asXML());
		$this->submit();
		if(!$this->genGZ()){
			echo('couldnt chmod the gz file.');
		}
		$this->genRobot();
		return true;
	}

	private function write($content){
		require_once(ABSPath.'/lib/php/pear/System.php');
		if(!file_exists(ABSPath.'/'.$this->file_name)){
			$handle = fopen(ABSPath.'/'.$this->file_name,'w');
			fwrite($handle,$content);
		}else{
			@System::rm('-r '.ABSPath.'/'.$this->file_name);
			$handle = fopen(ABSPath.'/'.$this->file_name,'w');
			fwrite($handle,$content);
		}
		fclose($handle);
		return true;
	}
    private function submit($site = 'http://www.google.com/webmasters/sitemaps/ping')
    {
    	global $siteConfig;
    	$url = $site.'?sitemap='.htmlentities($this->siteUrl.'/'.$this->file_name).'';
    	$result = $this->fetch_remote_file($url,$this->proxy,$this->proxy_port);
    	$code = $result->status;
        if ($code != 200) {
             die($result->error);
        }elseif($code == 200){
        	print $this->siteUrl.'/'.$this->file_name.' has been submitted successfuly to '.$site.'';
        }
    }

	private function fetch_remote_file ($url, $proxyHost= NULL , $proxyPort = NULL,$headers = "" ) {
		require_once(ABSPath.'/lib/php/Snoopy.class.inc');
		$client = new Snoopy();
		$client->rawheaders["Pragma"] = "no-cache";
		$client->agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.14) Gecko/20080404 Firefox/3.0.5';
		if(($proxyHost != NULL) && ( $proxyPort != NULL )){
			$client->proxy_host = $proxyHost;
			$client->proxy_port = $proxyPort;
		}
		@$client->fetch($url);
		return $client;
	}

    private function genGZ(){
    	require_once(ABSPath.'/lib/php/pear/File/Archive.php');
    	$files = array(ABSPath.'/'.$this->file_name);
		File_Archive::extract(
		    $files,
		    File_Archive::toArchive(
		        ABSPath."/sitemap.xml.gz",
		         File_Archive::toFiles()
		    )
		);
		return chmod('sitemap.xml.gz',0666);
    }
    private function genRobot(){
    	global $siteConfig;
 		require_once(ABSPath.'/lib/php/pear/System.php');
 		$content ="User-Agent: *
Allow: /*
Sitemap: ".$this->siteUrl."/sitemap.xml
Sitemap: ".$this->siteUrl."sitemap.xml.gz";
		if(!file_exists(ABSPath.'/robots.txt')){
			$handle = fopen(ABSPath.'/robots.txt','w');
			fwrite($handle,$content);
		}else{
			@System::rm('-r '.ABSPath.'/robots.txt');
			$handle = fopen(ABSPath.'/robots.txt','w');
			fwrite($handle,$content);
		}
		fclose($handle);
		return true;
    }
}
?>