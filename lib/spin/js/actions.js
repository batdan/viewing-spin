/**
 * Séléction des textes
 */
function selectText(elem)
{
    var id = $(elem).attr('id');

    // Source (form | modal | fullscreen)
    var source  = parentSource(id);

    // On désélectionne tous les textes qui n'ont pas le même idParent
    deselectTextOtherParent(elem);

    // Fermeture de toutes les modals d'édition de spin
    if (source != 'modal') {
        ms_close_all();
    }

	// Sélection / désélection d'un bloc de texte
    $(elem).toggleClass("on off");

	// Sélection limité à deux bloc
	countText(elem);

	// Sélection des objets situés entre deux textes sélectionnés
	autoSelectText(elem);
}


/**
 * Validation de l'initialisation d'un masterspin
 */
$('.spin').on('click', '.valid-init-spin', function(event) {

	var parent 	= $(this).parent().parent().parent();
	spinid 		= parent.attr('spinid');
	lastmodif	= parent.attr('lastmodif');

	// Récupération du nom du champ de spin
	var id = $(this).attr('id');
	id = id.split('_');
	id = id[1];

	var text = $('#chpInit_' + id).val();

	// Chaine vide = pas d'initialisation du spin
	if (text.length == 0) {

		$('#chpInit_' + id).attr('placeholder', 'Veuillez insérer un texte...');

		setTimeout( function() {
			$('#chpInit_' + id).attr('placeholder', 'Initialisation du spin...');
		}, 3000);

	} else {

		$.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
		{
			action		: 'initSpin',
	        source      : 'form',
			spinid		: spinid,
			lastmodif	: lastmodif,
			elemid1		: id,
			text		: text
		},
		function success(data)
		{
			// console.log(data);

            parent.html(data.html);

	        // Mise à jour des date et heure de modification d'un spin
	        majLastmodif(data.spinid, data.lastmodif);

            // Mise à jour du statut s'il s'agit d'un spin calculé
            majStatut(data.spinid, data.statut)

		}, 'json');
	}
});


/**
 * Edition d'un bloc de texte
 */
function editText(id)
{
    // Source (form | modal | fullscreen)
    var source  = parentSource(id);

    var elemid1 = cleanId(id);

	var idParent = $('#' + id).parent().attr('id');
	var spinid 	 = $('#' + idParent).attr('spinid');
	var lastmodif= $('#' + idParent).attr('lastmodif');
	var text	 = $('#cm_textEdit_' + id).val();

	$.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'editText',
        source      : source,
		spinid		: spinid,
		lastmodif	: lastmodif,
		elemid1		: elemid1,
		text		: text
	},
	function success(data)
	{
		// console.log(data);

        // MAJ du code html
        switch (source) {
            case 'form':
                $('#' + idParent).html(data.html);
                break;
            case 'modal':
                var split  = idParent.split('--');
                var idspin = split[0].split('__');
                var champ  = split[1].split('__');
                $('#ms_newcomb_' + champ[0] + '__' + idspin[1]).focus();

                $('#' + idParent).parent().parent().html(data.html);
                break;
        }

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Gestion des infobulles (tooltip)
        majTooltip();

	}, 'json');
}


/**
 * Edition d'un bloc de texte
 */
function addText(id, sibling)
{
    // Source (form | modal | fullscreen)
    var source  = parentSource(id);

    var elemid1 = cleanId(id);

	var idParent = $('#' + id).parent().attr('id');
	var spinid 	 = $('#' + idParent).attr('spinid');
	var lastmodif= $('#' + idParent).attr('lastmodif');
	var text	 = $('#cm_addText_' + id).val();

	$.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'addText',
        source      : source,
		spinid		: spinid,
		lastmodif	: lastmodif,
		elemid1		: elemid1,
		text		: text,
		sibling		: sibling
	},
	function success(data)
	{
		// console.log(data);

        // MAJ du code html
        switch (source) {
            case 'form':
                $('#' + idParent).html(data.html);
                break;
            case 'modal':
                $('#' + idParent).parent().parent().html(data.html);
                break;
        }

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Gestion des infobulles (tooltip)
        majTooltip();

	}, 'json');
}


/**
 * Split d'un bloc de texte
 */
function splitText(id)
{
    // Source (form | modal | fullscreen)
    var source  = parentSource(id);

	var elemid1 = cleanId(id);

	var idParent = $('#' + id).parent().attr('id');
	var spinid 	 = $('#' + idParent).attr('spinid');
	var lastmodif= $('#' + idParent).attr('lastmodif');

	$.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'splitText',
        source      : source,
		spinid		: spinid,
		lastmodif	: lastmodif,
		elemid1		: elemid1
	},
	function success(data)
	{
		// console.log(data);

        // MAJ du code html
        switch (source) {
            case 'form':
                $('#' + idParent).html(data.html);
                break;
            case 'modal':
                $('#' + idParent).parent().parent().html(data.html);
                break;
        }

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Gestion des infobulles (tooltip)
        majTooltip();

	}, 'json');
}


/**
 * Fusion de plusieurs blocs de texte
 */
function fusionText(id)
{
    // Source (form | modal | fullscreen)
    var source  = parentSource(id);

	var idParent = $('#' + id).parent().attr('id');
	var spinid 	 = $('#' + idParent).attr('spinid');
	var lastmodif= $('#' + idParent).attr('lastmodif');

	var elem1 	 = $('#' + idParent + '>div[class="on"]').get(0);
	var elem2 	 = $('#' + idParent + '>div[class="on"]').get(1);

	var elemid1  = cleanId( $(elem1).attr('id') );
	var elemid2  = cleanId( $(elem2).attr('id') );

	$.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'fusionText',
        source      : source,
		spinid		: spinid,
		lastmodif	: lastmodif,
		elemid1		: elemid1,
		elemid2		: elemid2
	},
	function success(data)
	{
		// console.log(data);

        // MAJ du code html
        switch (source) {
            case 'form':
                $('#' + idParent).html(data.html);
                break;
            case 'modal':
                $('#' + idParent).parent().parent().html(data.html);
                break;
        }

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Gestion des infobulles (tooltip)
        majTooltip();

	}, 'json');
}


/**
 * Suppression d'un bloc de texte
 */
function supprText(id)
{
    // Source (form | modal | fullscreen)
    var source  = parentSource(id);

	var elemid1  = cleanId(id);

	var idParent = $('#' + id).parent().attr('id');
	var spinid 	 = $('#' + idParent).attr('spinid');
	var lastmodif= $('#' + idParent).attr('lastmodif');

	$.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'supprText',
        source      : source,
		spinid		: spinid,
		lastmodif	: lastmodif,
		elemid1		: elemid1
	},
	function success(data)
	{
		// console.log(data);

        // MAJ du code html
        switch (source) {
            case 'form':
                $('#' + idParent).html(data.html);
                break;
            case 'modal':
                $('#' + idParent).parent().parent().html(data.html);
                break;
        }

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Gestion des infobulles (tooltip)
        majTooltip();

	}, 'json');
}


/**
 * Activation d'un spin
 */
$('.spin').on('click', '.activSpin', function() {

    var posX = $(this).attr('posX');

    var id = $(this).attr('id');
	id = id.substr(14, (id.length - 14));

    // Source (form | modal | fullscreen)
    var source  = parentSource(id);

	var idParent = $('#' + id).parent().attr('id');
	var spinid 	 = $('#' + idParent).attr('spinid');
	var lastmodif= $('#' + idParent).attr('lastmodif');

	var elem1 	 = $('#' + idParent + '>div[class="on"]').get(0);
	var elemid1  = cleanId( $(elem1).attr('id') );

	var prefix	 = prefixId( $(elem1).attr('id') );

	var elemid2  = '';
	if ($('#' + idParent + '>div[class="on"]').length == 2) {
		var elem2 	= $('#' + idParent + '>div[class="on"]').get(1);
		var elemid2 = cleanId( $(elem2).attr('id') );
	}

	$.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'activSpin',
        source      : source,
		spinid		: spinid,
		lastmodif	: lastmodif,
		elemid1		: elemid1,
		elemid2		: elemid2
	},
	function success(data)
	{
		// console.log(data);

        // MAJ du code html
        switch (source) {
            case 'form':
                $('#' + idParent).html(data.html);
                break;
            case 'modal':
                $('#' + idParent).parent().parent().html(data.html);
                break;
        }

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

		// Affichage de la modal d'edition de spin
        // console.log(prefix);
        // console.log(data.other.spinid);

		modal_editSpin( $('#' + prefix + '__' + data.other.spinid ), posX);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Gestion des infobulles (tooltip)
        majTooltip();

	}, 'json');
});


/**
 * Désactivation d'un spin
 */
function desactivSpin(id)
{
    // Source (form | modal | fullscreen)
    var source  = parentSource(id);

	var idParent = $('#' + id).parent().attr('id');
	var spinid 	 = $('#' + idParent).attr('spinid');
	var lastmodif= $('#' + idParent).attr('lastmodif');

    // id du spin parent
    var elemSpinId = '';
    if (source == 'modal') {
        var elemSpinId   = $('#' + id).parent().parent().parent().parent().attr('idspin');
        elemSpinId       = elemSpinId.split('__');
        elemSpinId       = elemSpinId[1];
    }

	var elemid1  = cleanId(id);

	$.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'desactivSpin',
        source      : source,
		spinid		: spinid,
		lastmodif	: lastmodif,
		elemid1		: elemid1,
        elemSpinId  : elemSpinId
	},
	function success(data)
	{
		// console.log(data);

        // MAJ du code html
        switch (source) {
            case 'form':
                $('#' + idParent).html(data.html);
                break;
            case 'modal':
                $('#' + idParent).parent().parent().html(data.html);
                break;
        }

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Gestion des infobulles (tooltip)
        majTooltip();

	}, 'json');
}


/**
 * Spin - Ajout d'une variante
 */
function add_comb(id)
{
    var idParent = $('#ms_' + id).parent().attr('id');
	var spinid 	 = $('#' + idParent).attr('spinid');
	var lastmodif= $('#' + idParent).attr('lastmodif');

	var elemid1  = cleanId(id);
	var text	 = $('#ms_newcomb_' + id).val();

	$.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'addComb',
        source      : 'modal',
		spinid		: spinid,
		lastmodif	: lastmodif,
		elemid1		: elemid1,
        elemSpinId  : elemid1,
		text		: text

	},
	function success(data)
	{
		// console.log(data);

        // MAJ du code html de la modal
        $('#modal__' + elemid1 + '--' + id).html(data.html);

        // Mise à jour du nombre de possibilités du masterspin
        $('#potential__' + spinid).attr('data-original-title', data.other.potential);

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Vide le textarea
        $('#ms_newcomb_' + id).val('');

        // Focus sur le textarea
        $('#ms_newcomb_' + id).focus();

        // Gestion des infobulles (tooltip)
        majTooltip();

	}, 'json');
}


/**
 * Spin - Suppression d'une variante
 */
$('.spin').on('click', '.delete-comb', function(event) {

    var id = $(this).attr('idspin');

    var idParent = $('#ms_' + id).parent().attr('id');
	var spinid 	 = $('#ms_' + id).parent().attr('spinid');
	var lastmodif= $('#ms_' + id).parent().attr('lastmodif');

	var elemid1    = $(this).attr('deletecombid');
    var elemSpinId = cleanId(id);

	$.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'removeComb',
        source      : 'modal',
		spinid		: spinid,
		lastmodif	: lastmodif,
		elemid1		: elemid1,
        elemSpinId  : elemSpinId
	},
	function success(data)
	{
		// console.log(data);

        // MAJ du code html de la modal
        $('#modal__' + elemSpinId + '--' + id).html(data.html);

        // Mise à jour du nombre de possibilités du masterspin
        $('#potential__' + spinid).attr('data-original-title', data.other.potential);

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Focus sur le textarea
        $('#ms_newcomb_' + id).focus();

        // Gestion des infobulles (tooltip)
        majTooltip();

	}, 'json');
});


/**
 * Spin - Suppression d'un spin
 */
function supprSpin(id)
{
    // Source (form | modal | fullscreen)
    var source  = parentSource(id);

	var idParent = $('#' + id).parent().attr('id');
	var spinid 	 = $('#' + idParent).attr('spinid');
	var lastmodif= $('#' + idParent).attr('lastmodif');

	var elemid1  = cleanId(id);

	$.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'supprSpin',
        source      : source,
		spinid		: spinid,
		lastmodif	: lastmodif,
		elemid1		: elemid1
	},
	function success(data)
	{
		// console.log(data);

        // MAJ du code html
        switch (source) {
            case 'form':
                $('#' + idParent).html(data.html);
                break;
            case 'modal':
                $('#' + idParent).parent().parent().html(data.html);
                break;
        }

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Gestion des infobulles (tooltip)
        majTooltip();

	}, 'json');
}


/**
 * Fermeture de la modal d'édition de spin
 */
function closeSpinMajFirstComb(elem)
{
    var id = $(elem).attr('id');

    // Source (form | modal | fullscreen)
    var source  = parentSource(id);

    var idParent = $(elem).parent().attr('id');
    var spinid 	 = $('#' + idParent).attr('spinid');
    var lastmodif= $('#' + idParent).attr('lastmodif');

    var elemid1  = cleanId(id);

    $.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'closeSpinMajFirstComb',
        source      : source,
		spinid		: spinid,
		lastmodif	: lastmodif,
		elemid1		: elemid1
	},
	function success(data)
	{
		// console.log(data);

        // Fermeture de la modal
        switch (source) {
            case 'form':
                // Fermture modal
                $('#ms_' + id).remove();
                break;
            case 'modal':
                var realid  = id.split('--');
                var idModal = 'ms_' + realid[1];
                var spin  = $('#' + idModal).prev();
                var spinParent = spin.parent().parent().parent();
                var idTextarea = spinParent.attr('id').split('--');

                // Focus sur le textarea
                $('#ms_newcomb_' + idTextarea[1]).focus();

                // Fermeture modal
                $('#' + idModal).remove();

                break;
        }

        // Mise à jour du texte de spin en prenant la première comb
        $(elem).html(data.html);
        $(elem).attr('class', 'spin_off');

	}, 'json');
}


/**
 * Supprimer le contenu d'un masterspin
 */
function eraseSpin(spinid, lastmodif)
{
    $.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'eraseSpin',
        source      : 'form',
		spinid		: spinid,
		lastmodif	: lastmodif
	},
	function success(data)
	{
		// console.log(data);

        // Mise à jour du spin
        $('[spinid="' + spinid + '"][class="spin no-select"]').html(data.html);

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Fermeture de la modal
        $.fancybox.close();

    }, 'json');
}


/**
 * Minifier le contenu d'un masterspin
 */
function minifySpin(spinid, lastmodif)
{
    $.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'minifySpin',
        source      : 'form',
		spinid		: spinid,
		lastmodif	: lastmodif
	},
	function success(data)
	{
		// console.log(data);

        // Mise à jour du spin
        $('[spinid="' + spinid + '"][class="spin no-select"]').html(data.html);

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Fermeture de la modal
        $.fancybox.close();

        // Gestion des infobulles (tooltip)
        majTooltip();

    }, 'json');
}




/**
 * Scinder tous les champs texte du masterspin
 */
function splitAll(spinid, lastmodif)
{
    $.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'splitAll',
        source      : 'form',
		spinid		: spinid,
		lastmodif	: lastmodif
	},
	function success(data)
	{
		// console.log(data);

        // Mise à jour du spin
        $('[spinid="' + spinid + '"][class="spin no-select"]').html(data.html);

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Fermeture de la modal
        $.fancybox.close();

        // Gestion des infobulles (tooltip)
        majTooltip();

    }, 'json');
}


/**
 * Historique (prev|next)
 */
function historique(spinid, lastmodif)
{
    $.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'historique',
        source      : 'form',
		spinid		: spinid,
		lastmodif	: lastmodif
	},
	function success(data)
	{
		// console.log(data);

        // Mise à jour du spin
        $('[spinid="' + spinid + '"][class="spin no-select"]').html(data.html);

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Gestion des infobulles (tooltip)
        $('[role="tooltip"]').remove();
        majTooltip();

    }, 'json');
}


/**
 * Spin calculé : retour à l'édition
 */
function spinCalculEdit(spinid, lastmodif)
{
    $.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'spinCalculEdit',
        source      : 'form',
		spinid		: spinid,
		lastmodif	: lastmodif
	},
	function success(data)
	{
		// console.log(data);

        // Mise à jour du spin
        $('[spinid="' + spinid + '"][class="spin no-select"]').html(data.html);

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Fermeture de la modal
        $.fancybox.close();

        // Gestion des infobulles (tooltip)
        majTooltip();

    }, 'json');
}


/**
 * Spin calculé : envoi au calcul
 */
function spinCaclulOn(spinid, lastmodif)
{
    $.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'spinCaclulOn',
        source      : 'form',
		spinid		: spinid,
		lastmodif	: lastmodif
	},
	function success(data)
	{
		// console.log(data);

        // Mise à jour du spin
        $('[spinid="' + spinid + '"][class="spin no-select"]').html(data.html);

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Fermeture de la modal
        $.fancybox.close();

        // Gestion des infobulles (tooltip)
        majTooltip();

    }, 'json');
}


/**
 * Spin calculé : calcul terminé
 */
function spinCaclulEnd(spinid, lastmodif)
{
    $.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'spinCaclulEnd',
        source      : 'form',
		spinid		: spinid,
		lastmodif	: lastmodif
	},
	function success(data)
	{
		// console.log(data);

        // Mise à jour du spin
        $('[spinid="' + spinid + '"][class="spin no-select"]').html(data.html);

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Fermeture de la modal
        $.fancybox.close();

        // Gestion des infobulles (tooltip)
        majTooltip();

    }, 'json');
}
