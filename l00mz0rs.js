// Automatic link processing

function ext_links()
{
	lc = document.getElementsByTagName('A');

	for( x = 0; x < lc.length; x++ ) {
		if( lc[x].href ) {	// FF kludge
		// IE
		h = document.all ? lc[x].host.substring(0, lc[x].host.length - 3) : lc[x].host;

		if( !lc[x].target && lc[x].href.substring(0, 4) == 'http' && h != location.host ) {
				lc[x].target = '_blank';

				if( lc[x].className ) {
					lc[x].className += ' ';
				}
				lc[x].className += 'external';
		}
		}	// FF kludge
	}
}

// Automatic caption generator

function captions()
{
	for( i = 0; i < document.images.length; i++ ) {
		aS = document.images[i].alt.indexOf('[');
		aE = document.images[i].alt.indexOf(']');

		if( aS > -1 && aE > -1 ) {
			pN = document.images[i].parentNode;

			div = document.createElement('div');

			div.className = 'caption';

			if( document.images[i].align == 'left' || document.images[i].align == 'right' ) {
				if( document.all ) {
					div.style.styleFloat = document.images[i].align;
				}
				else {
					div.style.cssFloat = document.images[i].align;
				}
				document.images[i].align = 'bottom';
			}

			if ( document.images[i].width ) {
				div.style.width = document.images[i].width + 'px';
			}

			div.appendChild( document.images[i] );
			div.appendChild( document.createTextNode( div.firstChild.alt.substring( ++aS, aE )));

			pN.insertBefore( div, pN.firstChild );
		}
	}
}

if( document.all ) {
	window.attachEvent('onload', captions);
	window.attachEvent('onload', ext_links);
}
else {
	window.addEventListener('load', captions, false);
	window.addEventListener('load', ext_links, false);
}
