<?php 
if(!function_exists('device_type'))
{
	function device_type($type)
	{
		if(!empty($type))
		{
			if($type == 'Android')
			{
				$tp = '0';
			}else if($type == 'iOS')
			{
				$tp = '1';
			}else{
				$tp = '2';
			}
			return $tp;
		}
	}
}