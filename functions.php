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
		$a[] = $sw->getMeanDirection()->getValue();
		$a[] = "degrees";

		if ($var = $sw->getDirectionVariations())
		{
			$a[] = "variable between";
			$a[] = $var[0]->getValue();
			$a[] = "and";
			$a[] = $var[count($var)-1]->getValue();
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

		if ($clouds = $d->getClouds())
		{
			foreach ($clouds as $cloud)
			{
				$a[] = $cloud->getAmount();
				$a[] = "at";
				$a[] = "{" . $cloud->getBaseHeight()->getValue() . "}";
				$a[] = "feet";

				if ($type = $cloud->getType())
				{
					$a[] = $type;
				}
			}
		}
		else
		{
			$a[] = "NSC";
		}

	}

	return $a;
}

function getTemperature($temp)
{
	$a = array();

	if ($temp < 0)
		$a[] = "minus";

	$a[] = abs($temp);

	return $a;
}