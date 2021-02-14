<?php
/**
 * 	<?xml version="1.0" encoding="UTF-8"?>
 *	<masterspin spinid="abcde1" chp="content" lastmodif="2015-08-11 08:18:12" lastid="10">
 * 		<spin id="1">
 * 			<comb id="2">
 * 				<text id="3">bla1</text>
 * 				<text id="4">bla2</text>
 * 				<spin id="5">
 * 					<comb id="6">
 * 						<text id="7">bla3</text>
 * 					</comb>
 * 					<comb id="8">
 * 						<text id="9">bla4</text>
 * 						<text id="10">bla5</text>
 * 					</comb>
 * 				</spin>
 * 			</comb>
 * 		</spin>
 *	</masterspin>
 */

$xml = <<<eof
<?xml version="1.0" encoding="UTF-8"?>
<masterspin spinid="abc1" chp="content" lastmodif="2015-08-11 08:18:12" lastid="3">
	<comb id="2">
		<text id="3">Ma voiture est bleue. </text>
		<text id="4">Ma moto est verte.</text>
	</comb>
</masterspin>
eof;
