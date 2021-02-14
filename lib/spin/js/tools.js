/**
 * Permet de savoir si on est dans le champ de formulaire, une modal,
 * ou en fullscreen (form | modal | fullscreen)
 */
function parentSource(id)
{
	return $('#' + id).parent().attr('source');
}


/**
 * Permet de positionner exactement contextMenus sous la souris
 */
function recupPosX(elem, event)
{
	var clickPosX 	= event.pageX;

	var posParent 	= $(elem).parent().parent().offset().left;
	var posXParent 	= Math.round(posParent);

	return clickPosX - posXParent;
}


/**
 * Mise à jour des date et heure de modification d'un spin
 */
function majLastmodif(spinid, lastmodif)
{
	$('div[spinid="' + spinid + '"]').attr('lastmodif', lastmodif);
}


/**
 * Mise à jour du statut pour passer un spin calculé en lecture seule
 * s'il est en 'calcul_on' ou 'calcul_end'
 */
function majStatut(spinid, statut)
{
	if (statut == '') {
		if ($('div[spinid="' + spinid + '"]').attr('statut')) {
			$('div[spinid="' + spinid + '"]').removeAttr('statut');
		}
	} else {
		$('div[spinid="' + spinid + '"]').attr('statut', statut);
	}
}


/**
 * Récupération du statut d'un spin s'il est calculé
 * Si la méthode renvoie 'true', le spin est éditable
 * dans le cas contraire il passe en readonly
 */
function checkStatutSpinCalc(elem)
{
	var statut 		= '';
	var contenaire 	= $(elem).closest('div[spinid][source="form"]');

	if (contenaire.attr('statut')) {
		statut = contenaire.attr('statut');
	}

	if (statut == '' || statut == 'edit') {
		return true;
	} else {
		return false;
	}
}


/**
 * Ferme toutes les actions en cours des autres spin
 */
function initOtherSpin(elem)
{
	var id = '';

	$('[spinid]').each( function () {

		if ( $(this).attr('spinid') != $(elem).attr('spinid') ) {

			id = $(this).attr('id');

			$('#' + id + '>.on').attr('class', 'off');
			$('#' + id + '>.lock').attr('class', 'off').removeAttr('style');
			$('#' + id + '>.spin_on').attr('class', 'spin_off');
			$('#' + id + '>.cm, #' + id + '>.cm_edit, #' + id + '>.cm, #' + id + '>.cm_synonymes, #' + id + '>.cm_spin, #' + id + '>.cm_tag, #' + id + '>.modalspin').remove();
		}
	});
}


/**
 * TEXT - Fermeture contextMenus
 */
function cm_close(id)
{
	var elem = $('#' + id);

	if ($(elem).attr('class') == 'on') {
		$(elem).attr('class', 'off');
	}

	$('.cm, .cm_edit, .cm_synonymes').remove();

	// On limite la sélection à deux blocs texte
	countText(elem)

	// Sélection des objets situés entre deux textes sélectionnés
	autoSelectText(elem);
}


/**
 * SPIN - Fermeture contextMenus
 */
function cm_spin_close(id)
{
	var elem = $('#' + id);

	$(elem).attr('class', 'spin_off');
	$('.cm_edit, .cm_spin').remove();
}


/**
 * TAG - Fermeture contextMenus
 */
function cm_tag_close(id)
{
	var elem = $('#' + id);

	$('[class*="tag"]>div').removeAttr("style");
	$('[class*="tag"]').removeAttr("style");
	$('.cm_edit, .cm_tag').remove();
}


/**
 * SPIN - Fermeture modal édition de spin
 */
function ms_close(id)
{
	var elem 		= $('#' + id);
	var idParent	= $(elem).parent().attr('id');

	$(elem).attr('class', 'spin_off');
	$('.cm_edit, .cm_spin').remove();

	// Premier niveau, mise à jour du form
	if ( $(elem).parent().attr('spinid') && $(elem).parent().attr('source') == 'form' ) {
		loadHTML( $(elem).parent().attr('id') );
	}

	// Modal de niveau 2 ou plus
	if ( $('#ms_' + id).parent().attr('spinid') && $('#ms_' + id).parent().attr('source') == 'modal' ) {
		loadModalHTML( $('#ms_' + id).parent().parent().parent().parent().prev().attr('id') );
	}
}


/**
 * Fermeture de toutes les modals d'édition de spin du même niveau
 */
function ms_close_all(id)
{
	var idParent = $('#' + id).parent().attr('id');

	$('#' + idParent + '>div[class="spin_on"]').attr('class', 'spin_off');
	$('#' + idParent + '>div[class*="modalspin"]').remove();
}


/**
 * Prefix d'un id - Nom du champ
 */
function prefixId(id)
{
	var prefix = '';
	var source = parentSource(id);

	if (source == 'modal') {
		id = id.split('--');
		prefix += id[0] + '--';

		id = id[1];
	}

	var split = id.split('__');
	prefix += split[0];

	return prefix;
}


/**
 * Désélectionne tous les textes n'ayant pas le même parent
 */
function deselectTextOtherParent(elem)
{
	var id          = $(elem).attr('id');
    var idParent    = $(elem).parent().attr('id');

	$('.on, .lock').each( function() {
        if ( $(this).parent().attr('id') != idParent ) {
            $(this).attr('class', 'off');
        }
    });

    $('.off, .spin_off').each( function() {
        if ( $(this).parent().attr('id') != idParent ) {
            $(this).removeAttr('style');
        }
    });
}


/**
 * Empêche de sélectionner plus de 2 blocs de texte
 * Verouille les textes séléectionnables entre les balises tag
 */
function countText(elem)
{
	var id = $(elem).attr('id');
	var idParent = $(elem).parent().attr('id');

	if ($('#' + idParent + '>div[class="on"]').length == 2) {

		$('#' + idParent + '>div[class="off"]').attr('class', 'lock');

	} else if ($('#' + idParent + '>div[class="on"]').length == 1) {

		// Bride la zone des textes clickables entre les tags
		var zoneActive = recupZoneActive(elem);

		// console.log(zoneActive);

		if (zoneActive && zoneActive != '') {
			zoneActive = zoneActive.substr(1, zoneActive.length-1);
			zoneActive = zoneActive.split('|');
		} else {
			zoneActive = new Array();
		}

		$('#' + idParent + '>div[class="on"], #' + idParent + '>div[class="off"]').attr('class', 'lock');
		$(elem).attr('class', 'on');

		for (var i=0; i<zoneActive.length; i++) {
			if ( $('#' + zoneActive[i]).attr('class') == 'on' || $('#' + zoneActive[i]).attr('class') == 'lock') {
				$('#' + zoneActive[i]).attr('class', 'off');
			}
		}

		// Ferme les modals d'edition de spin
		ms_close_all(id);

	} else {

		$('#' + idParent + '>div[class="lock"]').attr('class', 'off');

	}
}


/**
 * Liste les blocs text entre deux tags ou la debut et la fin du texte
 */
function recupZoneActive(elem, firstElem, sibling)
{
	if (!firstElem) { firstElem = elem; }
	if (!sibling)	{ sibling = 'prev'; }

	var node = null;
	if ($(elem).prev() && sibling=='prev') { node = $(elem).prev(); }
	if ($(elem).next() && sibling=='next') { node = $(elem).next(); }

	var idNewNode;
	var idUnique;
	var tagDeb;
	var tagFin;
	var idTag;
	var fermante;

	var result = '';
	var i=0;

	if (node.get(0)) {

		// Ne noeud suivant/précédent n'est pas une balise HTML
		if ( node.attr('class') && node.attr('class').indexOf('tag') == -1) {
			result += '|';
			result += node.attr('id');

			idNewNode = recupZoneActive( node, firstElem, sibling );
			if (idNewNode) { result += idNewNode; }
			return result;
		}

		if ( node.get(0).nodeName == 'BR') {
			idNewNode = recupZoneActive( node, firstElem, sibling );
			if (idNewNode) { result += idNewNode; }
			return result;
		}

		// Ne noeud suivant/précédent EST une balise HTML
		if ( node.attr('class') && node.attr('class').indexOf('tag') > -1) {

			if (sibling=='prev') {

				idUnique = node.attr('idunique');
				tagDeb 	 = $('[idunique="' + idUnique + '"]').get(0);
				idTag 	 = $(tagDeb).attr('id');

				if ( $(tagDeb).prev()) {

					// Balises non fermantes
					if ($('[idunique="' + node.attr('idunique') + '"]').length == 1) {

						idNewNode = recupZoneActive( $(tagDeb), firstElem, 'prev');
						if (idNewNode) { result += idNewNode; }
						return result;
					}

					// Balises fermantes
					if ($('[idunique="' + node.attr('idunique') + '"]').length == 2 && node.attr('id').indexOf('øfin') > -1) {
						idNewNode = recupZoneActive( $(tagDeb).prev(), firstElem, 'prev');
						if (idNewNode) { result += idNewNode; }
						return result;
					} else {
						// bascule vers les éléments suivants
						idNewNode = recupZoneActive( firstElem, firstElem, 'next');
						if (idNewNode) { result += idNewNode; }
						return result;
					}
				} else {
					// bascule vers les éléments suivants
					idNewNode = recupZoneActive( firstElem, firstElem, 'next');
					if (idNewNode) { result += idNewNode; }
					return result;
				}
			}

			if (sibling=='next') {

				idUnique = node.attr('idunique');

				fermante = 1;
				if ( $('[idunique="' + idUnique + '"]').length == 1) {
					fermante = 0;
				}

				if ( $(tagFin).next()) {

					// Balises non fermantes
					if (fermante == 0) {
						idNewNode = recupZoneActive( node, firstElem, 'next');
						if (idNewNode) { result += idNewNode; }
						return result;

					// Balises fermantes
					} else {
						if (node.attr('id').indexOf('øfin') == -1) {
							tagFin 	 = $('[idunique="' + idUnique + '"]').get(1);
							idTag 	 = $(tagFin).attr('id');

							idNewNode = recupZoneActive( $(tagFin).next(), firstElem, 'next');
							if (idNewNode) { result += idNewNode; }
							return result;
						}
					}
				}
			}
		}
	}

	// bascule vers les éléments suivants
	if (!node.get(0) && sibling=='prev') {
		idNewNode = recupZoneActive( firstElem, firstElem, 'next');
		if (idNewNode) { result += idNewNode; }
		return result;
	}
}


/**
 * Fait le pont entre deux éléments de type texte sélectionnés
 */
function autoSelectText(elem)
{
	var idParent = $(elem).parent().attr('id');

	if ($('#' + idParent + '>div[class="on"]').length == 2) {

		var text1 = $('#' + idParent + '>div[class="on"]').get(0);
		var text2 = $('#' + idParent + '>div[class="on"]').get(1);

		while (text1.nextSibling.id != text2.id) {

			text1 = text1.nextSibling;

			// Subrillance des blocs de texte
			if (text1.getAttribute('class') == 'lock' || text1.getAttribute('class') == 'off') {
				text1.setAttribute('style', 'background:#666; color:#fff;');
			}

			// Surbrillance des spins
			if (text1.getAttribute('class') == 'spin_off') {
				text1.setAttribute('style', 'background:#666; color:#89ccff;');
			}
		}
	} else {
		$('#' + idParent + '>div[class="off"]').each( function() 		{ this.removeAttribute('style'); });
		$('#' + idParent + '>div[class="lock"]').each( function() 		{ this.removeAttribute('style'); });
		$('#' + idParent + '>div[class="spin_off"]').each( function() 	{ this.removeAttribute('style'); });
	}
}


/**
 * Ne conserve que le bloc de texte séléctionné
 */
function oneTextSelected(elem)
{
    var idParent = $(elem).parent().attr('id');

    $('#' + idParent + '>div[class="on"]').each( function() {
        if (this.id != $(elem).attr('id')) {
            $(this).attr('class', 'off');
        }
    });

    $('#' + idParent + '>div[class="lock"]').each( function() {
        if (this.id != $(elem).attr('id')) {
            $(this).attr('class', 'off');
            $(this).removeAttr('style');
        }
    });
}


/**
 * Délelectionne tous les blocs de texte
 */
function textDeselect(elem)
{
	var idParent = $(elem).parent().attr('id');

    $('#' + idParent + '>div[class="on"]').each( function() {
    	$(this).attr('class', 'off');
    });

    $('#' + idParent + '>div[class="lock"]').each( function() {
        $(this).attr('class', 'off');
    });

	$('#' + idParent + '>div[class="off"]').removeAttr('style');
}


/**
 * Recherche les caractères de ponctuation et les espaces dans une chaine
 */
function searchPontusSpaces(str)
{
	// Ponctuations
	var ponctus = [".",";",":","!","?",",","«","»","(",")",'"'];
	var result 	= false;

	if (str.length > 1) {

		for (var i=0; i<ponctus.length; i++) {
			if (str.indexOf(ponctus[i]) > -1) {
				result = true;
				break
			}
		}

		if (str.indexOf(' ') > -1) {
			result = true;
		}
	}

	return result;
}


/**
 * Supprime le nom du champ de l'id d'un élement - Exemple : spin1§10 -> 10
 */
function cleanId(id)
{
	// Source (form | modal | fullscreen)
    var source  = parentSource(id);

	if (source == 'modal') {
		id = id.split('--');
		id = id[1];
	}

	var id = id.split('__');
	return id[1];
}


/**
 * TextArea - Toggle nombre de ligne textarea 1, 3 pi 6
 */
$('.spin').on('click', '.cm_nb_rows, .ms_nb_rows', function() {

    var id = $(this).attr('id');
    id = id.substr(11, (id.length - 11));

	if ($('#cm_addText_' + id).length) 	{ var textarea = $('#cm_addText_' + id);  }
	if ($('#cm_textEdit_' + id).length)	{ var textarea = $('#cm_textEdit_' + id); }
	if ($('#ms_newcomb_' + id).length)	{ var textarea = $('#ms_newcomb_' + id);  }

	// On modifie manuellement la hauteur de la modal car si elle est déplacée
	// elle ne se resize plus automatiquement

	var nbRows = textarea.attr('rows');

	switch(nbRows)
	{
		case '1' : textarea.attr('rows', 3);		break;
		case '3' : textarea.attr('rows', 6);		break;
		case '6' : textarea.attr('rows', 9);		break;
		case '9' : textarea.attr('rows', 1);		break;
	}

    textarea.select();
});


/**
 * Largeur de la modal 250px, 450px ou 800px
 */
$('.spin').on('click', '.cm_width, .ms_width', function() {

	var btn 		= $(this);
	var classbtn 	= btn.attr('class');
	var modal;

	var id = btn.attr('id');
    id = id.substr(9, (id.length - 9));

	if ($('#cm_addText_' + id).length) 	{ var textarea = $('#cm_addText_' + id);  }
	if ($('#cm_textEdit_' + id).length)	{ var textarea = $('#cm_textEdit_' + id); }
	if ($('#ms_newcomb_' + id).length)	{ var textarea = $('#ms_newcomb_' + id);  }

	// Modals de spin
	if (classbtn.indexOf('ms_width') > -1) {

		modal = btn.closest('div[class*="modalspin"]');

		switch (modal.css('width'))
		{
			case '1000px': modal.css('width', '300px');		break;
			case '300px' : modal.css('width', '500px');		break;
			case '500px' : modal.css('width', '800px');		break;
			case '800px' : modal.css('width', '1000px');	break;
		}
	}

	if (classbtn.indexOf('cm_width') > -1) {

		modal = btn.closest('div[class*="cm_edit"]');

		switch (modal.css('width'))
		{
			case '1000px': modal.css('width', '300px');		break;
			case '300px' : modal.css('width', '450px');		break;
			case '450px' : modal.css('width', '650px');		break;
			case '650px' : modal.css('width', '800px');		break;
			case '800px' : modal.css('width', '1000px');	break;
		}
	}

	textarea.select();
});


/**
 * Mise en avant des balises HTML ouvrantes et fermantes
 */
$('.spin').on('mouseover', '.tag', function(event) {
	var idunique = $(this).attr('idunique');
	$('[idunique="' + idunique + '"]').attr('style', 'color:#222;');
});
$('.spin').on('mouseout', '.tag', function(event) {
	var idunique = $(this).attr('idunique');
	$('[idunique="' + idunique + '"]').removeAttr('style');
});


/**
 * Retourne un objet contenant tous les attributs d'un élément
 */
(function($) {
    $.fn.getAttributes = function() {
        var attributes = {};

        if( this.length ) {
            $.each( this[0].attributes, function( index, attr ) {
                attributes[ attr.name ] = attr.value;
            } );
        }

        return attributes;
    };
})(jQuery);


/**
 * Gestion des tootltip
 */
function majTooltip()
{
	$('[title]').tooltip({ container: 'body' });
}


/**
 * Première lettre en majuscule
 */
function ucfirst(str) {
	str += '';
	var f = str.charAt(0).toUpperCase();
	return f + str.substr(1);
}


/**
 * Première lettre en minuscule
 */
function ucmin(str) {
	str += '';
	var f = str.charAt(0).toLowerCase();
	return f + str.substr(1);
}


/**
 * Première lettre en majuscule + suppression des accents
 */
function ucfirstWithoutAccent(str) {
	str += '';
	var char0 = no_accent(str.charAt(0));
	var f = char0.toUpperCase();
	return f + str.substr(1);
}


/**
 * Suppression des accents
 */
function no_accent(my_string) {
	var new_string = my_string.replace(/[èéêë]/g, "e").replace(/[ç]/g, "c").replace(/[àâä]/g, "a").replace(/[ïî]/g, "i").replace(/[ûùü]/g, "u").replace(/[ôöó]/g, "o");
	return new_string;
}
