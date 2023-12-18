/***********************************************************************
 * randomAlphanmeric
 * creates random characters of alpha numeric chars
 *
 * dstObj               - the destination object to copy the data to
 * charsLength          - length of random characters
 * chars				- allowed chars
 ***********************************************************************/
function randomAlphanumeric(dstObj,charsLength,chars) {

	var dstElem = document.getElementById(dstObj);

	var length = charsLength;

	if (!chars)
		var chars = "abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789";

	var randomChars = "";

	for(x=0; x<length; x++) {
		var i = Math.floor(Math.random() * chars.length);
		randomChars += chars.charAt(i);
	}

	dstElem.value = randomChars;
}

function randomNumbers(dstObj,charsLength,chars) {

	var dstElem = document.getElementById(dstObj);

	var length = charsLength;

	if (!chars)
		var chars = "123456789";

	var randomChars = "";

	for(x=0; x<length; x++) {
		var i = Math.floor(Math.random() * chars.length);
		randomChars += chars.charAt(i);
	}

	dstElem.value = randomChars;
}

function randomCircuit(dstObj,charsLength,chars) {

	var dstElem = document.getElementById(dstObj);

	var length = charsLength;

	if (!chars)
		var chars = "123456789";

	var randomChars = "";

	for(x=0; x<length; x++) {
		var i = Math.floor(Math.random() * chars.length);
		randomChars += chars.charAt(i);
	}

	dstElem.value = ( 'CID' + randomChars );
}

function randomCircuitDWDM(dstObj,charsLength,chars) {

        var dstElem = document.getElementById(dstObj);

        var length = charsLength;

        if (!chars)
                var chars = "123456789";

        var randomChars = "";

        for(x=0; x<length; x++) {
                var i = Math.floor(Math.random() * chars.length);
                randomChars += chars.charAt(i);
        }

        dstElem.value = ( 'DWDM' + randomChars );
}

function randomCircuitDarkFiber(dstObj,charsLength,chars) {

        var dstElem = document.getElementById(dstObj);

        var length = charsLength;

        if (!chars)
                var chars = "123456789";

        var randomChars = "";

        for(x=0; x<length; x++) {
                var i = Math.floor(Math.random() * chars.length);
                randomChars += chars.charAt(i);
        }

        dstElem.value = ( 'DF' + randomChars );
}

function randomGroupCircuit(dstObj,charsLength,chars) {

        var dstElem = document.getElementById(dstObj);

        var length = charsLength;

        if (!chars)
                var chars = "123456789";

        var randomChars = "";

        for(x=0; x<length; x++) {
                var i = Math.floor(Math.random() * chars.length);
                randomChars += chars.charAt(i);
        }

        dstElem.value = ( 'GCID' + randomChars );
}