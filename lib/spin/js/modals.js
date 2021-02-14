/**
 * Modal - Edition d'un spin
 */
function modal_editSpin(elem, posX)
{
    // Récupération de l'ID
	var id       = $(elem).attr('id');

    var elemid1  = cleanId(id);
    var idParent = $('#' + id).parent().attr('id');
    var spinid 	 = $('#' + id).parent().attr('spinid');
    var lastmodif= $('#' + id).parent().attr('lastmodif');

    // Ferme menu contextuel ouverts
	$('.cm, .cm_edit, .cm_synonymes, .cm_spin').remove();

    // Déselection de tous les textes
    textDeselect(elem);

    // Passage en classe "on" (sélection)
	$(elem).attr('class', 'spin_on');

	var idModal = id

	// Gestion des modals de niveau 2 et plus
	if (id.split('--').length == 2) {
		idModal = id.split('--');
		idModal = idModal[1];
	}

    // Fermeture de cette modal si elle était déjà ouverte
	$('#ms_' + idModal).remove();

    //////////////////////////////////////////////////////////////////////////////////////////
    // Initialisation du bloc modalspin (ms)
    $('#' + id).after('<div id="ms_' + idModal + '" idspin="' + idModal + '" class="modalspin" style="left:' + (posX - 200) + 'px; width:500px;"></div>')
	var ms = $('#ms_' + idModal);
    ms.draggable({ axis: 'x' });
	ms.resizable({ handles: 'e, w' });

    //////////////////////////////////////////////////////////////////////////////////////////
    // Button modal / textarea
    ms.append('<div id="ms_buttons_' + idModal + '" class="buttons_container"></div>');
	var ms_buttons = $('#ms_buttons_' + idModal);

	//////////////////////////////////////////////////////////////////////////////////////////
	// Boutons à gauche
	ms_buttons.append('<div id="ms_buttons_left_' + idModal + '" class="buttons_left"></div>');
	var ms_buttons_left = $('#ms_buttons_left_' + idModal);

	/**
	 * Dans le cas d'un spin calculé, on désactive les boutons et le textarea
	 * s'il est en statut "calcul_on" ou "calcul_end"
	 */
	if (checkStatutSpinCalc(elem)) {

		// Boutons (group)
		ms_buttons_left.append('<div id="ms_buttons_left_group_' + idModal + '" class="btn-group" style="display:inline-block;" aria-label="btn-group-' + idModal + '"></div>');
		var ms_buttons_left_group = $('#ms_buttons_left_group_' + idModal);

		// Bouton - Largeur de la modal de spin 300px, 500px, 800px, ou 1000px
		ms_buttons_left_group.append('<button id="ms_width_' + idModal + '" type="button" class="btn btn-default btn-xs ms_width"><i class="fa fa-arrows-h"></i></button>');

		// Bouton - toggle nombre de ligne textarea 1, 3 ou 6
		ms_buttons_left_group.append('<button id="ms_nb_rows_' + idModal + '" type="button" class="btn btn-default btn-xs ms_nb_rows"><i class="fa fa-arrows-v"></i></button>');

		// Bouton - Ajout d'une nouvelle variante
		ms_buttons_left_group.append('<button id="ms_addcomb_' + idModal + '" type="button" class="btn btn-primary btn-xs ms_addcomb"><i class="fa fa-plus"></i></button>');

		//////////////////////////////////////////////////////////////////////////////////////////
		// Textarea - Ajout d'une variante
		ms.append('<textarea id="ms_newcomb_' + idModal + '" rows="3"></textarea>');
		var ms_newcomb = $('#ms_newcomb_' + idModal);
		ms_newcomb.focus();
		ms_newcomb.keypress(function(e) {
			if (e.keyCode === 27) { if (e.preventDefault) { e.preventDefault(); closeSpinMajFirstComb(elem); } }
			if (e.keyCode === 13) { if (e.preventDefault) { e.preventDefault(); add_comb(idModal); } }
		});
	}

	//////////////////////////////////////////////////////////////////////////////////////////
	// Boutons à droite
	ms_buttons.append('<div id="ms_buttons_right_' + idModal + '" align="right" class="buttons_right"></div>');
	var ms_buttons_right = $('#ms_buttons_right_' + idModal);

    // Bouton - Fermeture modal
    ms_buttons_right.append('<button id="ms_close_' + idModal + '" type="button" class="btn btn-default btn-xs ms_close"><i class="fa fa-times"></i></button>');

	//////////////////////////////////////////////////////////////////////////////////////////
    // Bloc contenant les variantes
	var idSpinComb = 'modal__' + elemid1 + '--' +  idModal;
	ms.append('<div id="' + idSpinComb + '"></div>');
    $('#' + idSpinComb).attr('class', 'spin_comb');

	// Affichage des variantes d'un spin
    loadModalHTML(id);
}


/**
 * Modal Edit Spin - Fermeture de la modal
 */
$('.spin').on('click', '.ms_close', function() {

	var elem = $(this).parent().parent().parent().prev();
	closeSpinMajFirstComb(elem);
});


/**
 * Modal Edit Spin - Ajout d'une nouvelle variante
 */
$('.spin').on('click', '.ms_addcomb', function() {

     var id = $(this).attr('id');
     id = id.substr(11, (id.length - 11));
     add_comb(id);
});
