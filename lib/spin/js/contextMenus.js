/**
 * TEXT - Menu contextuel Ligne1
 */
function menuText_Line1(elem, posX)
{
	var id = $(elem).attr('id');
	var idParent = $(elem).parent().attr('id');
	var txtMot = $(elem).html();

	// Bloc de boutons line1
	$('#cm_' + id).append('<div id="cm_l1_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l1"></div>');
	var cm_l1 = $('#cm_l1_' + id);

	// Nouveau Spin
	cm_l1.append('<button id="cm_l1_newSpin_' + id + '" type="button" class="btn btn-success activSpin" posX="' + posX + '" title="Nouveau Spin"><i class="fa fa-sitemap"></i></button>');

	// Editer le texte
	cm_l1.append('<button id="cm_l1_editText_' + id + '" type="button" class="btn btn-primary editText" posX="' + posX + '" title="Editer le texte"><i class="fa fa-pencil"></button>');

	// Splitter le texte
	cm_l1.append('<button id="cm_l1_splitText_' + id + '" type="button" class="btn btn-primary" title="Splitter le texte"></button>');
	var cm_l1_splitText = $('#cm_l1_splitText_' + id);
	cm_l1_splitText.attr('onclick', "splitText('" + id + "');");
	cm_l1_splitText.append('<i class="fa fa-ellipsis-h"></i>');
	if ((searchPontusSpaces(txtMot) == false || $('#' + idParent + '>div[class="on"]').length == 2) && txtMot.indexOf("'") == -1) {
		cm_l1_splitText.attr('disabled', 'disabled');
	}

	// Fusionner le texte
	if ($('#' + idParent + '>div[class="on"]').length > 1 ) {
		cm_l1.append('<button id="cm_l1_fusionText_' + id + '" type="button" class="btn btn-primary" title="Fusionner le texte"></button>');
		var cm_l1_fusionText = $('#cm_l1_fusionText_' + id);
		cm_l1_fusionText.attr('onclick', "fusionText('" + id + "');");
		cm_l1_fusionText.append('<i class="fa fa-minus"></i>');

	// Le dictionnaire n'étant disponible que sur un mot, on le met à la place de la fusion
	} else {
		cm_l1.append('<button id="cm_l1_synonymes_' + id + '" type="button" class="btn btn-primary synonymes" posX="' + posX + '" title="Trouver des synonymes"></button>');
		var cm_l1_synonymes = $('#cm_l1_synonymes_' + id);
		cm_l1_synonymes.append('<i class="fa fa-book"></i>');

		if ($('#' + id).html() == ' ') {
			cm_l1_synonymes.attr('disabled', 'disabled');
			cm_l1_synonymes.attr('title', 'Aucun synonyme trouvé');
		}
	}

	// Supprimer le texte
	cm_l1.append('<button id="cm_l1_supprText_' + id + '" type="button" class="btn btn-danger" title="Supprimer le texte"></button>');
	var cm_l1_supprText = $('#cm_l1_supprText_' + id);
	cm_l1_supprText.attr('onclick', "supprText('" + id + "');");
	cm_l1_supprText.append('<i class="fa fa-eraser"></i>');
	if ($('#' + idParent + '>div[class="on"]').length == 2) {
		cm_l1_supprText.attr('disabled', 'disabled');
	}
}

/**
 * TEXT - contextuel Ligne2
 */
function menuText_Line2(elem, posX)
{
	var id = $(elem).attr('id');

	var elemClass = $(elem).attr('class');
	if (elemClass == 'on' || elemClass == 'off') {
		elemType = 'text';
	}
	if (elemClass == 'spin_on' || elemClass == 'spin_off') {
		elemType = 'spin';
	}
	if (elemClass == 'tag tag-edit' || elemClass == 'tag-spe') {
		elemType = 'tag';
	}

	// Bloc de boutons line2a
	$('#cm_' + id).append('<div id="cm_l2_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l2" style="display:inline-block;"></div>');
	var cm_l2 = $('#cm_l2_' + id);

	// Ajout texte avant
	cm_l2.append('<button id="cm_l2_prependText_' + id + '" type="button" class="btn btn-primary" title="Ajout texte avant"></button>');
	var cm_l2_prependText = $('#cm_l2_prependText_' + id);
	cm_l2_prependText.attr('onclick', "cm_addText('" + id + "', " + posX + ", 'avant', '" + elemType + "');");
	cm_l2_prependText.append('<i class="fa fa-step-backward"></i>');

	// Ajout texte après
	cm_l2.append('<button id="cm_l2_appendText_' + id + '" type="button" class="btn btn-primary" title="Ajout texte après"></button>');
	var cm_l2_appendText = $('#cm_l2_appendText_' + id);
	cm_l2_appendText.attr('onclick', "cm_addText('" + id + "', " + posX + ", 'après', '" + elemType + "');");
	cm_l2_appendText.append('<i class="fa fa-step-forward"></i>');

	// Ajout variable avant
	cm_l2.append('<button id="cm_l2_prependVar_' + id + '" type="button" class="btn btn-info" title="Ajout variable avant"></button>');
	var cm_l2_prependVar = $('#cm_l2_prependVar_' + id);
	cm_l2_prependVar.attr('onclick', "cm_tag_spe_free('" + id + "', " + posX + ", 'avant', '" + elemType + "');");
	cm_l2_prependVar.append('<i class="fa fa-step-backward"></i>');

	// Ajout variable après
	cm_l2.append('<button id="cm_l2_appendVar_' + id + '" type="button" class="btn btn-info" title="Ajout variable après"></button>');
	var cm_l2_appendVar = $('#cm_l2_appendVar_' + id);
	cm_l2_appendVar.attr('onclick', "cm_tag_spe_free('" + id + "', " + posX + ", 'après', '" + elemType + "');");
	cm_l2_appendVar.append('<i class="fa fa-step-forward"></i>');

	// Ajout libre d'une balise
	cm_l2.append('<button idobjet="' + id + '" id="cm_l2_freeTag_' + id + '" type="button" class="btn btn-warning" title="Autres balises HTML"></button>');
	var cm_l2_freeTag = $('#cm_l2_freeTag_' + id);
	cm_l2_freeTag.attr('onclick', "cm_tag_free('" + id + "', " + posX + ", '');")
	cm_l2_freeTag.append('<i class="fa fa-code"></i>');
}

/**
 * TEXT - Menu contextuel Ligne3
 */
function menuText_Line3(elem, posX)
{
	var id = $(elem).attr('id');

	// Bloc de boutons line3
	$('#cm_' + id).append('<div id="cm_l3_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l3"></div>');
	var cm_l3 = $('#cm_l3_' + id);

	// Gras
	cm_l3.append('<button idobjet="' + id + '" id="cm_l3_bold_' + id + '" type="button" class="btn btn-default cm-tag" tag="strong" close="1" title="Gras"><i class="fa fa-bold"></i></button>');

	// Italic
	cm_l3.append('<button idobjet="' + id + '" id="cm_l3_italic_' + id + '" type="button" class="btn btn-default cm-tag" tag="em" close="1" title="Italic"><i class="fa fa-italic"></i></button>');

	// Souligné
	cm_l3.append('<button idobjet="' + id + '" id="cm_l3_underline_' + id + '" type="button" class="btn btn-default cm-tag" tag="u" close="1" title="Souligné"><i class="fa fa-underline"></i></button>');

	// Retour chariot
	cm_l3.append('<button idobjet="' + id + '" id="cm_l3_br_' + id + '" type="button" class="btn btn-default buttonText cm-tag" tag="br" close="0" title="Retour chariot"><span>br</span></button>');

	// Paragraphe
	cm_l3.append('<button idobjet="' + id + '" id="cm_l3_paragraph_' + id + '" type="button" class="btn btn-default cm-tag" tag="p" close="1" title="Paragraphe"><i class="fa fa-paragraph"></i></button>');
}

/**
 * TEXT - Menu contextuel Ligne4
 */
function menuText_Line4(elem, posX)
{
	var id = $(elem).attr('id');

	// Bloc de boutons line4
	$('#cm_' + id).append('<div id="cm_l4_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l4"></div>');
	var cm_l4 = $('#cm_l4_' + id);

	// balise <h1>
	cm_l4.append('<button idobjet="' + id + '" id="cm_l4_h1_' + id + '" type="button" class="btn btn-default buttonText cm-tag" tag="h1" close="1" title="balise h1"><span>h1</span></button>');

	// balise <h2>
	cm_l4.append('<button idobjet="' + id + '" id="cm_l4_h2_' + id + '" type="button" class="btn btn-default buttonText cm-tag" tag="h2" close="1" title="balise h2"><span>h2</span></button>');

	// balise <h3>
	cm_l4.append('<button idobjet="' + id + '" id="cm_l4_h3_' + id + '" type="button" class="btn btn-default buttonText cm-tag" tag="h3" close="1" title="balise h3"><span>h3</span></button>');

	// balise <h4>
	cm_l4.append('<button idobjet="' + id + '" id="cm_l4_h4_' + id + '" type="button" class="btn btn-default buttonText cm-tag" tag="h4" close="1" title="balise h4"><span>h4</span></button>');

	// balise <h5>
	cm_l4.append('<button idobjet="' + id + '" id="cm_l4_h5_' + id + '" type="button" class="btn btn-default buttonText cm-tag" tag="h5" close="1" title="balise h5"><span>h5</span></button>');
}

/**
 * TEXT - Menu contextuel Ligne5
 */
function menuText_Line5(elem, posX)
{
	var id = $(elem).attr('id');

	// Bloc de boutons line5
	$('#cm_' + id).append('<div id="cm_l5_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l5"></div>');
	var cm_l5 = $('#cm_l5_' + id);

	// balise <ul>
	cm_l5.append('<button idobjet="' + id + '" id="cm_l5_ul_' + id + '" type="button" class="btn btn-default buttonText cm-tag" tag="ul" close="1" title="balise ul"><span>ul</span></button>');

	// balise <li>
	cm_l5.append('<button idobjet="' + id + '" id="cm_l5_li_' + id + '" type="button" class="btn btn-default buttonText cm-tag" tag="li" close="1" title="balise li"><span>li</span></button>');

	// balise <hr>
	cm_l5.append('<button idobjet="' + id + '" id="cm_l5_hr_' + id + '" type="button" class="btn btn-default buttonText cm-tag" tag="hr" close="0" title="balise hr"><span>hr</span></button>');

	// balise <a>
	cm_l5.append('<button idobjet="' + id + '" id="cm_l5_a_' + id + '" type="button" class="btn btn-default" title="Lien"></button>');
	var cm_l5_a = $('#cm_l5_a_' + id);
	cm_l5_a.attr('onclick', "cm_tag_free('" + id + "', " + posX + ", 'a');")
	cm_l5_a.append('<i class="fa fa-link" style="color:blue"></i>');

	// balise <img>
	cm_l5.append('<button idobjet="' + id + '" id="cm_l5_img_' + id + '" type="button" class="btn btn-default" title="Image"></button>');
	var cm_l5_img = $('#cm_l5_img_' + id);
	cm_l5_img.attr('onclick', "cm_tag_free('" + id + "', " + posX + ", 'img');")
	cm_l5_img.append('<i class="fa fa-picture-o" style="color:green"></i>');
}


/**
 * TEXT - Affichage du menu contextuel
 */
function cm_text(elem, event, posX)
{
	// Récupération de l'ID
	var id = $(elem).attr('id');

	// On désélectionne tous les textes qui n'ont pas le même idParent
    deselectTextOtherParent(elem);

	// Fermeture de toutes les modals d'édition de spin
	ms_close_all(id);

	// Ferme menu contextuel ouverts
	$('.cm, .cm_edit, .cm_synonymes, .cm_spin, .cm_tag').remove();

	// Désélection des spins et tags de même niveau
	var idParent = $(elem).parent().attr('id');
	$('#' + idParent + '>.spin_on').attr('class', 'spin_off');
	$('#' + idParent + '>[class*="tag"]>div').removeAttr("style");
	$('#' + idParent + '>[class*="tag"]').removeAttr("style");

	// Passage en classe "on" (sélection)
	$(elem).attr("class", "on");

	// Limitation de la sélection à deux blocs texte
	// Remet les lock en off si un seul texte est séléctionné
	countText(elem);

	// Sélection des objets situés entre deux textes sélectionnés
	autoSelectText(elem);

	// Initialisation du bloc
	$('#' + id).after('<div id="cm_' + id + '"></div>');
	var cm = $('#cm_' + id)
	cm.attr('idnode', id);
	cm.attr('class', 'cm');
	cm.attr('style', 'left:' + posX + 'px; width:180px;');

	// Affichage des lignes de boutons
	menuText_Line1(elem, posX);
	menuText_Line2(elem, posX);
	menuText_Line3(elem, posX);
	menuText_Line4(elem, posX);
	menuText_Line5(elem, posX);
}


/**
 * SPIN - Menu contextuel - Ligne1
 */
function menuSpin_Line1(elem)
{
	var id = $(elem).attr('id');
	var cm = $('#cm_' + id)

	// Désactiver le Spin
	cm.append('<button id="cm_l1_deleteSpin_' + id + '" type="button" class="btn btn-danger btn-sm" title="Désactiver le Spin"></button>');
	var cm_l1_deleteSpin = $('#cm_l1_deleteSpin_' + id)
	cm_l1_deleteSpin.attr('onclick', "desactivSpin('" + id + "');");
	cm_l1_deleteSpin.append('<i class="fa fa-sitemap"></i>');

	// Separateur
	cm.append('<div style="display:inline-block; width:98px;">&nbsp;</div>');

	// Supprimer le spin
	cm.append('<button id="cm_l1_supprSpin_' + id + '" type="button" class="btn btn-danger btn-sm" title="Supprimer le spin"></button>');
	var cm_l1_supprSpin = $('#cm_l1_supprSpin_' + id)
	cm_l1_supprSpin.attr('onclick', "supprSpin('" + id + "');");
	cm_l1_supprSpin.append('<i class="fa fa-eraser"></i>');
}

/**
 * SPIN - Affichage du menu contextuel
 */
function cm_spin(elem, event, posX)
{
	// Récupération de l'ID
	var id = $(elem).attr('id');

	// On désélectionne tous les textes qui n'ont pas le même idParent
    deselectTextOtherParent(elem);

	// Fermeture de toutes les modals d'édition de spin
	ms_close_all(id);

	// Ferme menu contextuel ouverts
	$('.cm, .cm_edit, .cm_synonymes, .cm_spin, .cm_tag').remove();

	// Désélection des spins et tags
	var idParent = $(elem).parent().attr('id');
	$('#' + idParent + '>.spin_on').attr('class', 'spin_off');
	$('#' + idParent + '>[class*="tag"]>div').removeAttr("style");
	$('#' + idParent + '>[class*="tag"]').removeAttr("style");

	// Passage en classe "spin_on" (sélection)
	$(elem).attr("class", "spin_on");

	// Déselection de tous les blocs de texte
	textDeselect(elem);

	// Sélection des objets situés entre deux textes sélectionnés
	autoSelectText(elem);

	// Initialisation du bloc
	$('#' + id).after('<div id="cm_' + id + '"></div>');
	var cm = $('#cm_' + id)
	cm.attr('idnode', id);
	cm.attr('class', 'cm_spin');
	cm.attr('style', 'left:' + posX + 'px; width:180px;');

	// Affichage des lignes de boutons
	menuSpin_Line1(elem);
	menuText_Line2(elem, posX);
	menuText_Line3(elem, posX);
	menuText_Line4(elem, posX);
	menuText_Line5(elem, posX);
}


/**
 * TAG - Menu contextuel - Ligne1
 */
function menuTag_Line1(elem, posX)
{
	var id = $(elem).attr('id');
	var cm = $('#cm_' + id)

	// Editer le tag
	cm.append('<button id="cm_l1_editTag_' + id + '" type="button" class="btn btn-primary btn-sm" title="Editer la balise"></button>');
	var cm_l1_editTag = $('#cm_l1_editTag_' + id);
	cm_l1_editTag.attr('onclick', "editTag('" + id + "', " + posX + ");");
	cm_l1_editTag.append('<i class="fa fa-pencil"></i>');

	// Separateur
	cm.append('<div style="display:inline-block; width:98px;">&nbsp;</div>');

	// Supprimer le Tag
	cm.append('<button id="cm_l1_deleteTag_' + id + '" type="button" class="btn btn-danger btn-sm" title="Supprimer la balise"></button>');
	var cm_l1_deleteTag = $('#cm_l1_deleteTag_' + id);
	cm_l1_deleteTag.attr('onclick', "deleteTag('" + id + "');");
	cm_l1_deleteTag.append('<i class="fa fa-code"></i>');
}

/**
 * TAG - Affichage du menu contextuel
 */
function cm_tag(elem, event, posX)
{
	// Récupération de l'ID
	var id = $(elem).attr('id');
	var idunique = $(elem).attr('idunique');

	// On désélectionne tous les textes qui n'ont pas le même idParent
    deselectTextOtherParent(elem);

	// Fermeture de toutes les modals d'édition de spin
	ms_close_all(id);

	// Ferme menu contextuel ouverts
	$('.cm, .cm_edit, .cm_synonymes, .cm_spin, .cm_tag').remove();

	// Déselection de tous les blocs de texte
	textDeselect(elem);

	// Désélection des spins
	var idParent = $(elem).parent().attr('id');
	$('#' + idParent + '>.spin_on').attr('class', 'spin_off');

	// Gestion des états enfoncés des balises HTML
	if ($(elem).attr('class') == 'tag tag-edit') {

		// Désélection des tags
		$('#' + idParent + '>[class*="tag"]>div').removeAttr("style");

		// Passage en classe "spin_on" (sélection)
		$('[idunique="' + idunique + '"]>div').attr("style", "color:#222;");

		// Sélection des objets situés entre deux textes sélectionnés
		autoSelectText(elem);

	// Gestion des états enfoncés des balises spécifiques
	} else {

		// Désélection des tags
		$('#' + idParent + '>[class*="tag"]').removeAttr("style");

		// Passage en classe "spin_on" (sélection)
		$('[idunique="' + idunique + '"]').attr("style", "background:linear-gradient(to bottom, #666, #111); color:#fff; cursor:pointer;");
	}


	// Initialisation du bloc
	$('#' + id).after('<div id="cm_' + id + '"></div>');
	var cm = $('#cm_' + id)
	cm.attr('idnode', id);
	cm.attr('class', 'cm_tag');
	cm.attr('style', 'left:' + posX + 'px; width:180px;');

	// Affichage des lignes de boutons
	menuTag_Line1(elem,  posX);
	menuText_Line2(elem, posX);
	menuText_Line3(elem, posX);
	menuText_Line4(elem, posX);
	menuText_Line5(elem, posX);
}
