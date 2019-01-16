<?php
/**
 * Automatic ATIS generator PHP script for vACCHUN
 * Created by Donat Marko (VATSIM CID 1389983)
 * @author     Donat Marko <sdf@donatus.hu>
 * @copyright  2019 Donat Marko | www.donatus.hu
 * @license    https://www.gnu.org/licenses/gpl.txt  GNU General Public License v3
 */

function getTime($time)
{
	$time = str_replace(":", "", $time);
	$time = str_replace(" UTC", "", $time);	
	return $time;
}

function getRunways($rwys)
{
	$rwys = explode(",", $rwys);

	$a = array();
	for ($i = 0; $i < count($rwys); $i++)
	{
		if (count($rwys) > 1 && $i == count($rwys) - 1)
			$a[] = "and";

		$a[] = $rwys[$i];
	}

	return $a;
}

function getTransitionLevel($lower, $qnh, $upper)
{
	if ($qnh <= 1013)
		return $upper;
	else
		return $lower;
}

function getSurfaceWinds($sw)
{
	$a = array();

	if ($sw->withVariableDirection() && $sw->getMeanSpeed()->getValue() <= 4)
	{
		$a[] = "light and variable";
	}
	elseif ($sw->getMeanSpeed()->getValue() == 0)
	{
		$a[] = "calm";
	}
	else
	{
		$a[] = sprintf("%03d", $sw->getMeanDirection()->getValue());
		$a[] = "degrees";

		if ($var = $sw->getDirectionVariations())
		{
			$a[] = "variable between";
			$a[] = sprintf("%03d", $var[0]->getValue());
			$a[] = "and";
			$a[] = sprintf("%03d", $var[count($var)-1]->getValue());
			$a[] = "degrees";
		}

		$a[] = $sw->getMeanSpeed()->getValue();
		$a[] = "knots";

		if ($gust = $sw->getSpeedVariations())
		{
			$a[] = "gusting";
			$a[] = $gust->getValue();
			$a[] = "knots";
		}
	}

	return $a;
}

function getVisibility($d)
{
	$a = array();

	if ($d->getCavok())
	{
		$a[] = "CAVOK";
	}
	elseif ($v = $d->getVisibility())
	{
		$a[] = "Visibility";
		$vis = $v->getVisibility()->getValue();
		if ($vis != 9999)
		{
			$a[] = $vis;
			$a[] = "meters";
		}
		else
		{
			$a[] = "10 kms or more";
		}
	}

	return $a;
}

function getClouds($clouds)
{
	$a = array();

	if ($clouds)
	{
		foreach ($clouds as $cloud)
		{
			if ($cloud->getAmount() == "VV")
			{
				$a[] = "Vertical visibility";
				$a[] = "{" . $cloud->getBaseHeight()->getValue() . "}";
				$a[] = "feet";
			}
			else
			{
				$a[] = $cloud->getAmount() . ($cloud->getType() ? " " . $cloud->getType() : "");
				$a[] = "{" . $cloud->getBaseHeight()->getValue() . "}";
				$a[] = "feet";	
			}
		}
	}
	else
	{
		$a[] = "NSC";
	}

	return $a;
}

function getTemperature($temp)
{
	$a = array();

	if ($temp < 0)
		$a[] = "minus";

	$a[] = sprintf("%02d", abs($temp));

	return $a;
}

function getQNH($qnh)
{
	return sprintf("%04d", $qnh);
}

function getRVR($rvrs)
{
	$a = array();

	if ($rvrs)
	{
		$a[] = "RVR";
		foreach ($rvrs as $rvr)
		{
			$a[] = "runway";
			$a[] = $rvr->getRunway();
			$a[] = $rvr->getVisualRange()->getValue();
			$a[] = "meters";
		}
	}

	return $a;
}

function getWeather($weathers)
{
	$a = array();

	for ($i = 0; $i < count($weathers); $i++)
	{
		if (count($weathers) > 1 && $i == count($weathers) - 1)
			$a[] = "and";

		$weather = $weathers[$i];
		$intensity = $weather->getIntensityProximity();
		
		if ($intensity == "+")
			$a[] = "heavy";
		if ($intensity == "-")
			$a[] = "light";

		$a[] = $weather->getCharacteristics();

		foreach ($weather->getTypes() as $type)
		{
			$a[] = $type;
		}

		if ($intensity == "VC")
			$a[] = "in vicinity";
	}

	return $a;
}