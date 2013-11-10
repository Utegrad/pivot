<?php
/**
 * echos the inline style on a cell for setting the background image to a gradient
 * 
 * @param float $value is the scale calculated from average score / max score
 */
function gbi($value){
	if($value != 0){
		echo "style='background-image: -webkit-gradient(
			linear,
			left top,
			right top,
			color-stop(0.15, #F0081B),
			color-stop(". ($value / 100) ."#F4F5A9)
			);
			background-image: -o-linear-gradient(right, #F0081B 15%, #F4F5A9 ". $value ."%);
			background-image: -moz-linear-gradient(right, #F0081B 15%, #F4F5A9 ". $value ."%);
			background-image: -webkit-linear-gradient(right, #F0081B 15%, #F4F5A9 ". $value ."%);
			background-image: -ms-linear-gradient(right, #F0081B 15%, #F4F5A9 ". $value ."%);
			background-image: linear-gradient(to right, #F0081B 15%, #F4F5A9 ". $value ."%);'";
	}
	else{
		echo "style='background-color: #f4f5a9;'";
	}
	
	
}
?>