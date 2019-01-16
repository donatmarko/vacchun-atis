<?php
/**
 * Automatic ATIS generator PHP script for vACCHUN
 * Created by Donat Marko (VATSIM CID 1389983)
 * @author     Donat Marko <sdf@donatus.hu>
 * @copyright  2019 Donat Marko | www.donatus.hu
 * @license    https://www.gnu.org/licenses/gpl.txt  GNU General Public License v3
 */

if (isset($_GET["tlcond"]))
{
	$raw_metar   = isset($_GET["metar"]) ? $_GET["metar"] : null;
	$rwy_arrival = isset($_GET["arr"])   ? $_GET["arr"]   : null;
	$rwy_depart  = isset($_GET["dep"])   ? $_GET["dep"]   : null;
	$app_type    = isset($_GET["app"])   ? $_GET["app"]   : null;
	$atis_letter = isset($_GET["info"])  ? $_GET["info"]  : null;
}
else
{
	$rwy_arrival = "13R";
	$rwy_depart = "13L,13R";
	$app_type = "ILS";
	$atis_letter = "Y";
}
$raw_metar = "LHBP 152300Z 26020KT 9999 +RA BR SCT032 BKN050 04/M01 Q1012";


require_once 'vendor/autoload.php';
require_once 'functions.php';
use MetarDecoder\MetarDecoder;

$a = array();
$decoder = new MetarDecoder();
$d = $decoder->parse($raw_metar);

if (!$d->isValid())
{
	die("Invalid METAR was given");
}

$a[] = $d->getIcao();
$a[] = "info";
$a[] = $atis_letter;

$a[] = "Observation at";
$a[] = getTime($d->getTime());

// Approach
$a[] = $app_type;
$a   = array_merge($a, getRunways($rwy_arrival));

// Departures
$a[] = "Departures rwy";
$a   = array_merge($a, getRunways($rwy_depart));

$a[] = "Transition level";
$a[] = getTransitionLevel(110, $d->getPressure()->getValue(), 120);

// Surface winds
$a[] = "Wind";
$a   = array_merge($a, getSurfaceWinds($d->getSurfaceWind()));

// Visibility
$a   = array_merge($a, getVisibility($d));

// Runway Visual Range
$a   = array_merge($a, getRVR($d->getRunwaysVisualRange()));

// Weather phenomenas
$a   = array_merge($a, getWeather($d->getPresentWeather()));

// Cloud base
$a   = array_merge($a, getClouds($d->getClouds()));

// Temperature
$a[] = "Temperature";
$a   = array_merge($a, getTemperature($d->getAirTemperature()->getValue()));

// Dew point
$a[] = "Dewpoint";
$a   = array_merge($a, getTemperature($d->getDewPointTemperature()->getValue()));

// QNH
$a[] = "QNH";
$a[] = getQNH($d->getPressure()->getValue());

$a[] = "On first contact notify receipt of";
$a[] = $atis_letter;






// Condition to test it in ES
if (isset($_GET["tlcond"]))
{
	foreach ($a as $s)
	{
		if (is_numeric($s) || strpos($s, "{") > -1)
		{
			print $s;
		}
		elseif (strlen($s) > 0)
		{
			print "[". $s . "]";
		}
	}
}
else
	print_r($a);
