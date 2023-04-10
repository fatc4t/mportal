<?php

function printer()
{

	$resource = printer_open();

	printer_set_option ($resource, PRINTER_ORIENTATION, PRINTER_ORIENTATION_PORTRAIT);

	printer_set_option ($resource, PRINTER_PAPER_FORMAT, PRINTER_FORMAT_A3);

	$printer_close($resource);

}
