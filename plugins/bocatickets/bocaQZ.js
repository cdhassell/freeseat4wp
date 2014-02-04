function freeseatPrint() {
	if (qz) {
		// Searches for locally installed printer with "Boca" in the name
		qz.findPrinter("BOCA");
		// Send characters/raw commands to applet using "append"
		// Hint:  Carriage Return = \r, New Line = \n, Escape Double Quotes= \"
		qz.append(bocaticketsText.output);
		// Mark the end of a label, in this case "<p>".
		// qz knows to look for this and treat this as the end of a "page"
		// for better control of larger spooled jobs
		qz.setEndOfDocument("<p>");
		// The amount of labels to spool to the printer at a time. When
		// qz counts this many `EndOfDocument`'s, a new print job will 
		// automatically be spooled to the printer and counting will start
		// over.
		qz.setDocumentsPerSpool("10");
		// Send characters/raw commands to printer
		qz.print();
		// applet.printToFile("~/jzebra_test.txt");
	} else {
		alert("Printer driver not found");
 	}
}

/**
* Automatically gets called when applet has loaded.
*/
function qzReady() {
	// Setup our global qz object
	// Change title when applet is ready	
	window["qz"] = document.getElementById('qz');
	var title = document.getElementById("bocawaiting");
	if (qz) {
		try {
			title.innerHTML = "<input type=button onClick='freeseatPrint()' value='Print' >";
		} catch(err) {  // LiveConnect error, display a detailed meesage
			alert("ERROR:  \nThe applet did not load correctly.  Communication to the " + 
				"applet has failed, likely caused by Java Security Settings.  \n\n" + 
				"CAUSE:  \nJava 7 update 25 and higher block LiveConnect calls " + 
				"once Oracle has marked that version as outdated, which " + 
				"is likely the cause.  \n\nSOLUTION:  \n  1. Update Java to the latest " + 
				"Java version \n          (or)\n  2. Lower the security " + 
				"settings from the Java Control Panel.");
		}
	}		
}

/**
* Returns whether or not the applet is not ready to print.
* Displays an alert if not ready.
*/
function notReady() {
	// If applet is not loaded, display an error
	if (!isLoaded()) {
		return true;
	}
	// If a printer hasn't been selected, display a message.
	else if (!qz.getPrinter()) {
		alert('No printer selected.');
		return true;
	}
	return false;
}

/**
* Returns if the applet is not loaded properly
*/
function isLoaded() {
	if (!qz) {
		alert('Error:\n\n\tPrint plugin is NOT loaded!');
		return false;
	} else {
		try {
			if (!qz.isActive()) {
				alert('Error:\n\n\tPrint plugin is loaded but NOT active!');
				return false;
			}
		} catch (err) {
			alert('Error:\n\n\tPrint plugin is NOT loaded properly!');
			return false;
		}
	}
	return true;
}

/**
* Automatically gets called when "qz.print()" is finished.
*/
function qzDonePrinting() {
	// Alert error, if any
	if (qz.getException()) {
		alert('Error printing:\n\n\t' + qz.getException().getLocalizedMessage());
		qz.clearException();
		return; 
	}
	
	// Alert success message
	alert('Successfully sent print data to "' + qz.getPrinter() + '" queue.');
}

function qzDoneFinding() {
	return;
}

