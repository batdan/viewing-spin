/**
 * Event - Edition d'un bloc texte
 */
$('.spin').on('click', '.editText', function(event) {

	var id 		 = $(this).attr('id');
	id = id.substr(15, (id.length - 15));

	var posX 	 = $(this).attr('posX');

	cm_editText(id, posX);
});


/**
 * Edition d'un bloc texte
 *
 * @param 	integer 	id		id du bloc de texte
 * @param	integer		posX	positionnement horizontal de la fenêtre
 */
function cm_editText(id, posX)
{
	var elem 	 = $('#' + id);
	var idParent = $(elem).parent().attr('id');

	// Ne conserve que le bloc de texte séléctionné
	if ($('#' + idParent + '>div[class="on"]').length == 2) {
		oneTextSelected(elem);
	}

	// Ferme le menu contextuel parent
	$('div[idnode="' + id + '"]').remove();

	// Bloc menu contextuel
	$('#' + id).after('<div id="cm_' + id + '" idnode="' + id + '" class="cm_edit" style="width:450px; left:' + (posX - 150) + 'px;"></div>')
	var cm = $('#cm_' + id);
	cm.draggable({ axis: 'x' });
	cm.resizable({ handles: 'e, w' });

	////////////////////////////////////////////////////////////////////////////
	// Boutons modal
	cm.append('<div id="cm_buttons_' + id + '" class="buttons_container"></div>');
	var cm_buttons = $('#cm_buttons_' + id);

	////////////////////////////////////////////////////////////////////////////
	// Bouton de gauche
	cm_buttons.append('<div id="cm_buttons_left_' + id + '" class="buttons_left"></div>');
	var cm_buttons_left = $('#cm_buttons_left_' + id);

	// Button Type de modal
	cm_buttons_left.append('<button type="button" class="btn btn-mylabel btn-xs" title="Editer le texte" style="margin-right:5px;"><i class="fa fa-pencil"></i></button>');

	// Bouton de gauche (group)
	cm_buttons_left.append('<div id="cm_buttons_left_group_' + id + '" class="btn-group" style="display:inline-block;" aria-label="btn-group-left-' + id + '"></div>');
	var cm_buttons_left_group = $('#cm_buttons_left_group_' + id);

	// Bouton - Largeur de la modal de spin 300px, 500px, 800px, ou 1000px
	cm_buttons_left_group.append('<button id="cm_width_' + id + '" type="button" class="btn btn-default btn-xs cm_width"><i class="fa fa-arrows-h"></i></button>');

	// Bouton hauteur textarea
	cm_buttons_left_group.append('<button id="cm_nb_rows_' + id + '" type="button" class="btn btn-default btn-xs cm_nb_rows"><i class="fa fa-arrows-v"></i></button>');

	////////////////////////////////////////////////////////////////////////////
	// Boutons de droite
	cm_buttons.append('<div id="cm_buttons_right_' + id + '" class="buttons_right" align="right"></div>');
	var cm_buttons_right = $('#cm_buttons_right_' + id);

	// Bouton - Fermeture modal
	cm_buttons_right.append('<button type="button" class="btn btn-default btn-xs" onclick="cm_close(\'' + id + '\');"><i class="fa fa-times"></i></button>');

	// textarea
	cm.append('<textarea id="cm_textEdit_' + id + '" class="col-lg-12" rows="3" style="padding:0 4px; margin-top:0px; resize:none;">' + $('#' + id).html() + '</textarea>');
	var cm_textEdit = $('#cm_textEdit_' + id);
	cm_textEdit.select();
	cm_textEdit.keypress(function(e) {
		if (e.keyCode === 13) { if (e.preventDefault) { e.preventDefault(); } editText(id); }
		if (e.keyCode === 27) { if (e.preventDefault) { e.preventDefault(); } cm_close(id); }
	});

	// Button group
	cm.append('<div id="cm_textEditGroup_' + id + '" class="pull-right btn-group btn-group-sm" role="group" aria-label="cm_l2a" style="margin:5px 1px 5px 0;"></div>');
	var cm_textEditGroup = $('#cm_textEditGroup_' + id);

	// Bouton Valider
	cm_textEditGroup.append('<input id="cm_textEditValid_' + id + '" type="button" class="btn btn-primary btn-sm" onclick="editText(\'' + id + '\');" value="Modifier">');
}


/**
 * Ajout de texte avant / après
 *
 * @param 	integer 	id		id du bloc de texte
 * @param	integer		posX	positionnement horizontal de la fenêtre
 * @param	string		txt		avant | après
 */
function cm_addText(id, posX, sibling, elemType)
{
	var elem 	 = $('#' + id);
	var idParent = $(elem).parent().attr('id');

	// Ne conserve que le bloc de texte séléctionné
	if ($('#' + idParent + '>div[class="on"]').length == 2) {
		oneTextSelected(elem);
	}

	// Ferme le menu contextuel parent
	$('div[idnode="' + id + '"]').remove();

	// Bloc menu contextuel
	$('#' + id).after('<div id="cm_' + id + '" idnode="' + id + '" class="cm_edit" style="width:650px; left:' + (posX - 150) + 'px;"><div>')
	var cm = $('#cm_' + id);
	cm.draggable({ axis: 'x' });
	cm.resizable({ handles: 'e, w' });

	// Titre
	var icone = 'fa fa-step-backward';
	if (sibling == 'après') {
		icone = 'fa fa-step-forward';
	}

	////////////////////////////////////////////////////////////////////////////
	// Boutons modal
	cm.append('<div id="cm_buttons_' + id + '" class="buttons_container"></div>');
	var cm_buttons = $('#cm_buttons_' + id);

	////////////////////////////////////////////////////////////////////////////
	// Bouton de gauche
	cm_buttons.append('<div id="cm_buttons_left_' + id + '" class="buttons_left"></div>');
	var cm_buttons_left = $('#cm_buttons_left_' + id);

	// Button Type de modal
	cm_buttons_left.append('<button type="button" class="btn btn-mylabel btn-xs" title="Insérer du texte ' + sibling + '" style="margin-right:5px;"><i class="' + icone + '"></i></button>');

	// Bouton de gauche (group)
	cm_buttons_left.append('<div id="cm_buttons_left_group_' + id + '" class="btn-group" style="display:inline-block;" aria-label="btn-group-left-' + id + '"></div>');
	var cm_buttons_left_group = $('#cm_buttons_left_group_' + id);

	// Bouton largeur
	cm_buttons_left_group.append('<button id="cm_width_' + id + '" type="button" class="btn btn-default btn-xs cm_width"><i class="fa fa-arrows-h"></i></button>');

	// Bouton hauteur textarea
	cm_buttons_left_group.append('<button id="cm_nb_rows_' + id + '" type="button" class="btn btn-default btn-xs cm_nb_rows"><i class="fa fa-arrows-v"></i></button>');

	////////////////////////////////////////////////////////////////////////////
	// Boutons de droite
	cm_buttons.append('<div id="cm_buttons_right_' + id + '" class="buttons_right" align="right"></div>');
	var cm_buttons_right = $('#cm_buttons_right_' + id);

	// Bouton - Fermeture modal
	cm_buttons_right.append('<button id="cm_addTextCancel_' + id + '" type="button" class="btn btn-default btn-xs"><i class="fa fa-times"></i></button>');
	var cm_addTextCancel = $('#cm_addTextCancel_' + id);
	if (elemType == 'text') {
		cm_addTextCancel.attr('onclick', "cm_close('" + id + "');");
	}
	if (elemType == 'spin') {
		cm_addTextCancel.attr('onclick', "cm_spin_close('" + id + "');");
	}
	if (elemType == 'tag') {
		cm_addTextCancel.attr('onclick', "cm_tag_close('" + id + "');");
	}

	// Texte de référence
	var texte = $('#' + id).html()
	texte = texte.replace(/ /g, '<div class="visu-space">&#9141;<span style="font-size:1px;"> </<span></div>');
	cm.append('<div class="col-lg-12 text-ref">' + texte + '</div>');

	// textarea
	cm.append('<textarea id="cm_addText_' + id + '" class="col-lg-12" rows="3" style="padding:0 4px; resize:none;"></textarea>');
	var cm_addText = $('#cm_addText_' + id);
	cm_addText.focus();
	cm_addText.keypress(function(e) {
		if (e.keyCode === 13) { if (e.preventDefault) { e.preventDefault(); } addText(id, sibling); }
		if (e.keyCode === 27) {
            if (e.preventDefault)       { e.preventDefault();   }

            if (elemType == 'text')     { cm_close(id);         }
            if (elemType == 'spin')     { cm_spin_close(id);    }
            if (elemType == 'tag')      { cm_tag_close(id);     }
        }
	});

	// Button group
	cm.append('<div id="cm_textEditGroup_' + id + '" class="pull-right btn-group btn-group-sm" role="group" aria-label="cm_l2a" style="margin:5px 1px 5px 0;"></div>');
	var cm_textEditGroup = $('#cm_textEditGroup_' + id);

	// Bouton Valider
	cm_textEditGroup.append('<input id="cm_addTextValid_' + id + '" type="button" class="btn btn-primary btn-sm" onclick="addText(\'' + id + '\', \'' + sibling + '\');" value="Insérer ' + sibling + '">');
}


/**
 * TAG - Menu contextuel Ligne1
 */
function menuTagFree_Line1(elem, posX)
{
	var id = $(elem).attr('id');

	$('#cm_' + id).append('<div id="cm_bloc_l1_' + id + '" style="display:block; height:36px;"></div>');

	// Ligne 1  - Bloc 1
	$('#cm_bloc_l1_' + id).append('<div id="cm_bloc_l1a_' + id + '" style="display:inline-block; margin:0 5px 0 0;"></div>');
	$('#cm_bloc_l1a_' + id).append('<div id="cm_l1a_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l1a"></div>');
	var cm_l1a = $('#cm_l1a_' + id);

	// Gras
	cm_l1a.append('<button idobjet="' + id + '" id="cm_l1a_bold_' + id + '" type="button" class="btn btn-default cm-tag-free" tag="strong" title="Gras"><i class="fa fa-bold"></i></button>');

	// Italic
	cm_l1a.append('<button idobjet="' + id + '" id="cm_l1a_italic_' + id + '" type="button" class="btn btn-default cm-tag-free" tag="em" title="Italic"><i class="fa fa-italic"></i></button>');

	// Souligné
	cm_l1a.append('<button idobjet="' + id + '" id="cm_l1a_underline_' + id + '" type="button" class="btn btn-default cm-tag-free" tag="u" title="Souligné"><i class="fa fa-underline"></i></button>');

	// Retour chariot
	cm_l1a.append('<button idobjet="' + id + '" id="cm_l1a_br_' + id + '" type="button" class="btn btn-default buttonText cm-tag-free" tag="br" title="Retour chariot"><span>br</span></button>');

	// Paragraphe
	cm_l1a.append('<button idobjet="' + id + '" id="cm_l1a_paragraph_' + id + '" type="button" class="btn btn-default cm-tag-free" tag="p" title="Paragraphe"><i class="fa fa-paragraph"></i></button>');

	// Ligne 1  - Bloc 2
	$('#cm_bloc_l1_' + id).append('<div id="cm_bloc_l1b_' + id + '" style="display:inline-block; margin:0;"></div>');
	$('#cm_bloc_l1b_' + id).append('<div id="cm_l1b_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l1b"></div>');
	var cm_l1b = $('#cm_l1b_' + id);

	// balise <h1>
	cm_l1b.append('<button idobjet="' + id + '" id="cm_l1b_h1_' + id + '" type="button" class="btn btn-default buttonText cm-tag-free" tag="h1" title="balise h1"><span>h1</span></button>');

	// balise <h2>
	cm_l1b.append('<button idobjet="' + id + '" id="cm_l1b_h2_' + id + '" type="button" class="btn btn-default buttonText cm-tag-free" tag="h2" title="balise h2"><span>h2</span></button>');

	// balise <h3>
	cm_l1b.append('<button idobjet="' + id + '" id="cm_l1b_h3_' + id + '" type="button" class="btn btn-default buttonText cm-tag-free" tag="h3" title="balise h3"><span>h3</span></button>');

	// balise <h4>
	cm_l1b.append('<button idobjet="' + id + '" id="cm_l1b_h4_' + id + '" type="button" class="btn btn-default buttonText cm-tag-free" tag="h4" title="balise h4"><span>h4</span></button>');

	// balise <h5>
	cm_l1b.append('<button idobjet="' + id + '" id="cm_l1b_h5_' + id + '" type="button" class="btn btn-default buttonText cm-tag-free" tag="h5" title="balise h5"><span>h5</span></button>');
}

/**
 * TAG - Menu contextuel Ligne2
 */
function menuTagFree_Line2(elem, posX)
{
	var id = $(elem).attr('id');

	$('#cm_' + id).append('<div id="cm_bloc_l2_' + id + '" style="display:block; height:36px;"></div>');

	// Ligne 2  - Bloc 1
	$('#cm_bloc_l2_' + id).append('<div id="cm_bloc_l2a_' + id + '" style="display:inline-block; margin:0 5px 0 0;"></div>');
	$('#cm_bloc_l2a_' + id).append('<div id="cm_l2a_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l2a"></div>');
	var cm_l2a = $('#cm_l2a_' + id);

	// balise <ul>
	cm_l2a.append('<button idobjet="' + id + '" id="cm_l2a_ul_' + id + '" type="button" class="btn btn-default buttonText cm-tag-free" tag="ul" title="balise ul"><span>ul</span></button>');

	// balise <li>
	cm_l2a.append('<button idobjet="' + id + '" id="cm_l2a_li_' + id + '" type="button" class="btn btn-default buttonText cm-tag-free" tag="li" title="balise li"><span>li</span></button>');

	// balise <hr>
	cm_l2a.append('<button idobjet="' + id + '" id="cm_l2a_hr_' + id + '" type="button" class="btn btn-default buttonText cm-tag-free" tag="hr" title="balise hr"><span>hr</span></button>');

	// balise <a>
	cm_l2a.append('<button idobjet="' + id + '" id="cm_l2a_a_' + id + '" type="button" class="btn btn-default cm-tag-free" tag="a" title="Lien"><i class="fa fa-link" style="color:blue"></i></button>');

	// balise <img>
	cm_l2a.append('<button idobjet="' + id + '" id="cm_l2a_img_' + id + '" type="button" class="btn btn-default cm-tag-free" tag="img" title="Image"><i class="fa fa-picture-o" style="color:green"></i></button>');
}


/**
 * Permet de précharger les tags dans le champ texte
 */
$('.spin').on('click', '.cm-tag-free', function(event) {

	var id  = $(this).attr('idobjet');
	var tag = $(this).attr('tag');

	if (tag == 'spe') {
		var variable = $(this).attr('variable');
		tagSpeList(id, variable);
	} else {
		tagList(id, tag);
	}

});


/**
 * Event - Recherche de synonymes
 */
$('.spin').on('click', '.synonymes', function(event) {

	var id 		 = $(this).attr('id');
	id = id.substr(16, (id.length - 16));

	var posX 	 = $(this).attr('posX');

	synonymes(id, posX);
});


/**
 * Event - Toggle checkbox synonymes
 */
(function($)
{
	$(function()
	{
		$('.spin').on('click', '#cm_synonyme_checkbox_full', function() {

			if ($('#cm_synonyme_checkbox_full').is(':checked')) {
				$('input[class="synonyme"][type="checkbox"]').prop('checked', true);
			} else {
				$('input[class="synonyme"][type="checkbox"]').prop('checked', false);
			}
		});
	});
})(jQuery);


/**
 * Recherche de synonymes
 */
function synonymes(id, posX)
{
	// Source (form | modal | fullscreen)
    var source   = parentSource(id);

	var elem 	 = $('#' + id);
	var idParent = $(elem).parent().attr('id');
	var spinid 	 = $('#' + idParent).attr('spinid');
	var lastmodif= $('#' + idParent).attr('lastmodif');

	var elem1 	 = $('#' + idParent + '>div[class="on"]').get(0);
	var elemid1  = cleanId( $(elem1).attr('id') );

	$.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'synonymes',
        source      : source,
		spinid		: spinid,
		lastmodif	: lastmodif,
		elemid1		: elemid1
	},
	function success(data)
	{
		// console.log(data);

		var synonymes = data.other;
		var countSyn  = synonymes.length;

		if (countSyn == 0) {

			var cm_l1_synonymes = $('#cm_l1_synonymes_' + id);
			cm_l1_synonymes.attr('disabled', 'disabled');
			cm_l1_synonymes.attr('title', 'Aucun synonyme trouvé');

		} else {

			// Ferme le menu contextuel parent
			$('div[idnode="' + id + '"]').remove();

			// Bloc menu contextuel
			$('#' + id).after('<div id="cm_' + id + '" idnode="' + id + '" class="cm_synonymes" style="width:200px; left:' + posX + 'px;"></div>')
			var cm = $('#cm_' + id);
			cm.draggable({ axis: 'x' });
			cm.resizable({ handles: 'e, w' });

			// Titre
			cm.append('&nbsp;<i class="fa fa-book"></i>&nbsp;&nbsp;Ajouter des synonymes');

			cm.append('<div id="cm_synonyme_full" class="col-lg-12" style="padding:0; color:#bbb; border-top:1px solid #ddd; padding-top:5px; padding-bottom:5px;"></div>');
			var cm_synonyme_full = $('#cm_synonyme_full');
			cm_synonyme_full.append('<input id="cm_synonyme_checkbox_full" type="checkbox" style="position:relative; top:2px;"> ');
			cm_synonyme_full.append('<div id="cm_synonyme_text_full" class="synonyme_text_full" style="display:inline-block; position:relative; top:-2px;"><i>tous | aucun</i></div>');

			for (var i=0; i<countSyn; i++) {
				cm.append('<div id="cm_synonyme_' + i + '" class="col-lg-12" style="padding:0; border-top:1px solid #ddd;"></div>');
				var cm_synonyme = $('#cm_synonyme_' + i);
				cm_synonyme.append('<input id="cm_synonyme_checkbox_' + i + '" class="synonyme" type="checkbox" style="position:relative; top:2px;"> ');
				cm_synonyme.append('<div id="cm_synonyme_text_' + i + '" class="synonyme_text" style="display:inline-block; position:relative; top:-2px;">' + synonymes[i] + '</div>');
			}

			// Button group
			cm.append('<div id="cm_addSyn_' + id + '" class="pull-right btn-group btn-group-sm" role="group" aria-label="cm_l2a" style="margin:5px 2px 8px 0;"></div>');
			var cm_addSyn = $('#cm_addSyn_' + id);

			// Bouton Annuler
			cm_addSyn.append('<input id="cm_addSynCancel_' + id + '" type="button" class="btn btn-default btn-sm" value="Annuler" onclick="cm_close(\'' + id + '\');">');

			// Bouton Valider
			var jsonSynonymes = JSON.stringify(synonymes);
			jsonSynonymes = jsonSynonymes.replace(/\'/g,"\\'"); // Echappement des simples quotes

			cm_addSyn.append('<input id="cm_addSynValid_' + id + '" type="button" value="Ajouter" class="btn btn-primary btn-sm">');
			var cm_addSynValid = $('#cm_addSynValid_' + id);
			cm_addSynValid.attr('onclick', "addSynonyme('" + id + "', " + posX + ", '" + jsonSynonymes + "');");
		}
	}, 'json');
}


function addSynonyme(id, posX, synonymes)
{
	// Source (form | modal | fullscreen)
    var source   = parentSource(id);

	var elem 	 = $('#' + id);
	var idParent = $(elem).parent().attr('id');
	var spinid 	 = $('#' + idParent).attr('spinid');
	var lastmodif= $('#' + idParent).attr('lastmodif');

	var elem1 	 = $('#' + idParent + '>div[class="on"]').get(0);
	var elemid1  = cleanId( $(elem1).attr('id') );

	var prefix	 = prefixId( $(elem1).attr('id') );

	var idChecked;
	var synonymesChecked = new Array();

	// Récupération de la liste de synonymes
	synonymes = jQuery.parseJSON(synonymes);

	// Récupération des synonymes cochés
	$('input[type="checkbox"][class="synonyme"]:checked').each( function() {

		idChecked = $(this).attr('id');
		idChecked = idChecked.substr(21, idChecked.length - 21);

		synonymesChecked.push( synonymes[idChecked] );
	});

	$.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action				: 'addSynonymes',
		source      		: source,
		spinid				: spinid,
		lastmodif			: lastmodif,
		elemid1				: elemid1,
		myArray				: synonymesChecked
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
}


// Liste des tags HTML
function tagList(id, tag)
{
	// Tableau des balise HTML
	var tagList 		= new Array();
	var tagCursorPos  	= new Array();

	tagList['strong']	= '<strong></strong>';
	tagList['em']  		= '<em></em>';
	tagList['u'] 		= '<u></u>';
	tagList['br']		= '<br>';
	tagList['p'] 		= '<p></p>';

	tagList['h1']		= '<h1></h1>';
	tagList['h2']		= '<h2></h2>';
	tagList['h3']		= '<h3></h3>';
	tagList['h4']		= '<h4></h3>';
	tagList['h5']		= '<h5></h5>';

	tagList['ul']		= '<ul></ul>';
	tagList['li']		= '<li></li>';
	tagList['hr']		= '<hr>';
	tagList['a']		= '<a href="" target="_blank"></a>';
	tagList['img']		= '<img src="">';

	tagCursorPos['a']		 = 9;
	tagCursorPos['img'] 	 = 10;

	var elem = $('#cm_tag_' + id);
	elem.val(tagList[tag]);

	if (tag == 'a' || tag == 'img') {
		elem.get(0).selectionStart = tagCursorPos[tag];
		elem.get(0).selectionEnd   = tagCursorPos[tag];
	}

	elem.focus();
}


// Liste des tags spécifiques
function tagSpeList(id, variable)
{
	// Tableau des balise HTML
	var tagList1 			= new Array();
	var tagList2 			= new Array();
	var tagCursorPos  		= new Array();

	tagList1['ville']		= '<spe var="ville">';
	tagList1['dep']			= '<spe var="dep">';
	tagList1['reg']			= '<spe var="reg">';

	tagList1['a_ville']		= '<spe var="a_ville">';
	tagList1['la_ville']	= '<spe var="la_ville">';
	tagList1['de_ville']	= '<spe var="de_ville">';

	tagList1['A_ville']		= '<spe var="a_ville" ucfirst="1">';
	tagList1['La_ville']	= '<spe var="la_ville" ucfirst="1">';
	tagList1['De_ville']	= '<spe var="de_ville" ucfirst="1">';

	tagList1['le_dep']		= '<spe var="le_dep">';
	tagList1['du_dep']		= '<spe var="du_dep">';
	tagList1['dans_le_dep']	= '<spe var="dans_le_dep">';

	tagList1['Le_dep']		= '<spe var="Le_dep" ucfirst="1">';
	tagList1['Du_dep']		= '<spe var="Du_dep" ucfirst="1">';
	tagList1['Dans_le_dep']	= '<spe var="Dans_le_dep" ucfirst="1">';

	tagList1['dep_num']			= '<spe var="dep_num">';
	tagList1['dans_le_dep_num']	= '<spe var="dans_le_dep_num">';

	tagList1['Dans_le_dep_num']	= '<spe var="Dans_le_dep_num" ucfirst="1">';

	tagList1['la_reg']		= '<spe var="la_reg">';
	tagList1['de_reg']		= '<spe var="de_reg">';
	tagList1['dans_la_reg']	= '<spe var="dans_la_reg">';

	tagList1['La_reg']		= '<spe var="La_reg" ucfirst="1">';
	tagList1['De_reg']		= '<spe var="De_reg" ucfirst="1">';
	tagList1['Dans_la_reg']	= '<spe var="Dans_la_reg" ucfirst="1">';

	tagList2['html'] 		= '<spe var="html" bloc="">';

	if (Object.keys(tagList1).indexOf(variable) > -1) {
		var elem = $('#cm_tag_' + id);
		elem.val(tagList1[variable]);

		$('#cm_tagValid_' + id).click();
	}

	if (Object.keys(tagList2).indexOf(variable) > -1) {
		var elem = $('#cm_tag_' + id);
		elem.val(tagList2[variable]);

		tagCursorPos['html'] = 22;

		if (variable == 'html') {
			elem.get(0).selectionStart = tagCursorPos[variable];
			elem.get(0).selectionEnd   = tagCursorPos[variable];
		}

		elem.focus();
	}
}


/**
 * Insertion balise HTML (formulaire libre)
 */
function cm_tag_free(id, posX, tag)
{
	var elem 	 = $('#' + id);
	var idParent = $(elem).parent().attr('id');

	// Vérification du type d'action : ajout ou modification
	var action 	 = 'ajout';
	var btnColor = 'primary';
	var btnTxt   = 'Valider';

	if (tag!='' && tag.substr(0,1)=='<') {

		action 	 = 'modif';
		btnColor = 'danger';
		btnTxt   = 'Modifier';
	}

	// Ferme le menu contextuel parent
	$('div[idnode="' + id + '"]').remove();

	// Bloc menu contextuel
	$('#' + id).after('<div id="cm_' + id + '" idnode="' + id + '" class="cm_edit" style="width:350px; min-width:350px; left:' + posX + 'px;"></div>');
	var cm = $('#cm_' + id);
	cm.draggable({ axis: 'x' });
	cm.resizable({ handles: 'e, w' });

	menuTagFree_Line1(elem, posX);
	menuTagFree_Line2(elem, posX);

	// Champ text : balise HTML
	cm.append('<div id="cm_group_tag_' + id + '" class="input-group col-lg-12" style="display:inline-block; margin-bottom:5px;"></div>');
	var cm_group_tag = $('#cm_group_tag_' + id);

	cm_group_tag.append('<input id="cm_tag_' + id + '" type="text" class="form-control" style="display:inline-block; width:calc(100% - 50px);" aria-describedby="cm_tag">');
	var cm_tag = $('#cm_tag_' + id)
	cm_tag.select();
	cm_tag.keypress(function(e) {
		if (e.keyCode === 13) { if (e.preventDefault) { e.preventDefault(); } tagFree(id, action);  			}
		if (e.keyCode === 27) { if (e.preventDefault) { e.preventDefault(); } cm_close(id); cm_tag_close(id)	}
	});

	// button group addon
	cm_group_tag.append('<span id="cm_tag" class="input-group-addon" style="display:inline-block; width:50px; line-height:20px;"><span id="cm_tagTxt">tag</tag></span>');

	// conteneur bottom modal
	cm.append('<div id="cm_tagBottom_' + id + '"></div>');
	var cm_tagBottom = $('#cm_tagBottom_' + id);

	// Message erreur
	cm_tagBottom.append('<div id="cm_tagError_' + id + '" style="margin:5px 0 5px 2px; color:red;" class="pull-left"></div>');

	// Conteneur boutons
	cm_tagBottom.append('<div id="cm_tagEdit_' + id + '" class="pull-right" style="margin:5px 0px 5px 0;"></div>');
	var cm_tagEdit = $('#cm_tagEdit_' + id);

	// Button group
	cm_tagEdit.append('<div id="cm_tagEditGroup_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l2a"></div>');
	var cm_tagEditGroup = $('#cm_tagEditGroup_' + id);

	// Bouton Annuler
	cm_tagEditGroup.append('<input id="cm_tagCancel_' + id + '" type="button" class="btn btn-default btn-sm" value="Annuler" onclick="cm_close(\'' + id + '\'); cm_tag_close(\'' + id + '\');">');

	// Bouton Valider
	cm_tagEditGroup.append('<input id="cm_tagValid_' + id + '" type="button" class="btn btn-' + btnColor + ' btn-sm" value="' + btnTxt + '" onclick="tagFree(\'' + id + '\', \'' + action + '\');">');

	// On charge les champs de l'éditeur de balises si des données sont envoyées
	if (tag == 'a' || tag == 'img') {
		tagList(id, tag);
	}

	// Modification d'un tag
	if (action == 'modif') {
		cm_tag.val(tag);
	}
}


/**
 * TAG SPE - Menu contextuel Ligne0
 */
function menuTagSpeFree_Line0(elem, posX)
{
	var id = $(elem).attr('id');

	$('#cm_' + id).append('<div id="cm_bloc_l0_' + id + '" style="display:block; height:36px;"></div>');

	// Ligne 1  - Bloc 1
	$('#cm_bloc_l0_' + id).append('<div id="cm_bloc_l0a_' + id + '" style="display:inline-block; margin:0;"></div>');
	$('#cm_bloc_l0a_' + id).append('<div id="cm_l0a_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l0a"></div>');
	var cm_l0a = $('#cm_l0a_' + id);

	// 'ville'
	cm_l0a.append('<button idobjet="' + id + '" id="cm_l0a_ville_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="ville"><span>ville</span></button>');

	// 'département'
	cm_l0a.append('<button idobjet="' + id + '" id="cm_l0a_dep_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="dep"><span>dép</span></button>');

	// 'région'
	cm_l0a.append('<button idobjet="' + id + '" id="cm_l0a_reg_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="reg"><span>rég</span></button>');
}


/**
 * TAG SPE - Menu contextuel Ligne1
 */
function menuTagSpeFree_Line1(elem, posX)
{
	var id = $(elem).attr('id');

	$('#cm_' + id).append('<div id="cm_bloc_l1_' + id + '" style="display:block; height:36px;"></div>');

	// Ligne 1  - Bloc 1
	$('#cm_bloc_l1_' + id).append('<div id="cm_bloc_l1a_' + id + '" style="display:inline-block; margin:0;"></div>');
	$('#cm_bloc_l1a_' + id).append('<div id="cm_l1a_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l1a"></div>');
	var cm_la1 = $('#cm_l1a_' + id);

	// à 'ville'
	cm_la1.append('<button idobjet="' + id + '" id="cm_l1a_a_ville_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="a_ville" tagMaj="1"><span>à ville</span></button>');

	// la 'ville'
	cm_la1.append('<button idobjet="' + id + '" id="cm_l1a_la_ville_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="la_ville" tagMaj="1"><span>la ville</span></button>');

	// de 'ville'
	cm_la1.append('<button idobjet="' + id + '" id="cm_l1a_de_ville_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="de_ville" tagMaj="1"><span>de ville</span></button>');
}

/**
 * TAG SPE - Menu contextuel Ligne2
 */
function menuTagSpeFree_Line2(elem, posX)
{
	var id = $(elem).attr('id');

	$('#cm_' + id).append('<div id="cm_bloc_l2_' + id + '" style="display:block; height:36px;"></div>');

	// Ligne 2  - Bloc 1
	$('#cm_bloc_l2_' + id).append('<div id="cm_bloc_l2a_' + id + '" style="display:inline-block; margin:0;"></div>');
	$('#cm_bloc_l2a_' + id).append('<div id="cm_l2a_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l2a"></div>');
	var cm_l2a = $('#cm_l2a_' + id);

	// le 'département'
	cm_l2a.append('<button idobjet="' + id + '" id="cm_l2a_le_dep_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="le_dep" tagMaj="1"><span>le dép</span></button>');

	// du 'département'
	cm_l2a.append('<button idobjet="' + id + '" id="cm_l2a_du_dep_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="du_dep" tagMaj="1"><span>du dép</span></button>');

	// dans le 'département'
	cm_l2a.append('<button idobjet="' + id + '" id="cm_l2a_ds_le_dep_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="dans_le_dep" tagMaj="1"><span>dans le dép</span></button>');
}

/**
 * TAG SPE - Menu contextuel Ligne2 bis
 */
function menuTagSpeFree_Line2b(elem, posX)
{
	var id = $(elem).attr('id');

	$('#cm_' + id).append('<div id="cm_bloc_l2b_' + id + '" style="display:block; height:36px;"></div>');

	// Ligne 2  - Bloc 1
	$('#cm_bloc_l2b_' + id).append('<div id="cm_bloc_l2ba_' + id + '" style="display:inline-block; margin:0;"></div>');
	$('#cm_bloc_l2ba_' + id).append('<div id="cm_l2ba_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l2ba"></div>');
	var cm_l2ba = $('#cm_l2ba_' + id);

	// le 'département' chiffres -> ex : 94
	cm_l2ba.append('<button idobjet="' + id + '" id="cm_l2ba_dep_num_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="dep_num"></button>');
	var cm_l2ba_dep_num = $('#cm_l2ba_dep_num_' + id);
	cm_l2ba_dep_num.attr('style', 'width:179px;');
	cm_l2ba_dep_num.append('<span>dép num</span>');

	// du 'département' chiffres -> ex : dans le 94
	cm_l2ba.append('<button idobjet="' + id + '" id="cm_l2ba_dans_le_dep_num_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="dans_le_dep_num" tagMaj="1"></button>');
	var cm_l2ba_dans_le_dep_num = $('#cm_l2ba_dans_le_dep_num_' + id);
	cm_l2ba_dans_le_dep_num.attr('style', 'width:179px;');
	cm_l2ba_dans_le_dep_num.append('<span>dans le dép num</span>');
}

/**
 * TAG SPE - Menu contextuel Ligne3
 */
function menuTagSpeFree_Line3(elem, posX)
{
	var id = $(elem).attr('id');

	$('#cm_' + id).append('<div id="cm_bloc_l3_' + id + '" style="display:block; height:36px;"></div>');

	// Ligne 3  - Bloc 1
	$('#cm_bloc_l3_' + id).append('<div id="cm_bloc_l3a_' + id + '" style="display:inline-block; margin:0;"></div>');
	$('#cm_bloc_l3a_' + id).append('<div id="cm_l3a_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l3a"></div>');
	var cm_l3a = $('#cm_l3a_' + id);

	// la 'région'
	cm_l3a.append('<button idobjet="' + id + '" id="cm_l3a_la_reg_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="la_reg" tagMaj="1"><span>la rég</span></button>');

	// de 'région'
	cm_l3a.append('<button idobjet="' + id + '" id="cm_l3a_de_reg_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="de_reg" tagMaj="1"><span>de rég</span></button>');

	// dans la 'région'
	cm_l3a.append('<button idobjet="' + id + '" id="cm_l3a_ds_la_reg_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="dans_la_reg" tagMaj="1"><span>dans la rég</span></button>');
}

/**
 * TAG SPE - Menu contextuel Ligne4
 */
function menuTagSpeFree_Line4(elem, posX)
{
	var id = $(elem).attr('id');

	$('#cm_' + id).append('<div id="cm_bloc_l4_' + id + '" style="display:block; height:36px;"></div>');

	// Ligne 3  - Bloc 1
	$('#cm_bloc_l4_' + id).append('<div id="cm_bloc_l4a_' + id + '" style="display:inline-block; margin:0 5px 0 0;"></div>');
	$('#cm_bloc_l4a_' + id).append('<div id="cm_l4a_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l4a"></div>');

	// Bloc HTML
	$('#cm_l4a_' + id).append('<button idobjet="' + id + '" id="cm_l4a_html_' + id + '" type="button" class="btn btn-default tagSpe cm-tag-free" tag="spe" variable="html"><span>html</span></button>');
}


/**
 * Insertion balise "spe" (géos, variables, etc.) (formulaire libre)
 */
function cm_tag_spe_free(id, posX, sibling, tag)
{
	var elem 	 = $('#' + id);
	var idParent = $(elem).parent().attr('id');
	var txtArea	 = 'var';

	if (sibling == 'avant') {
		txtArea = 'var <i class="fa fa-step-backward"></i>';
	} else if (sibling == 'après') {
		txtArea = '<i class="fa fa-step-forward"></i> var';
	}

	// Vérification du type d'action : ajout ou modification
	var action 	 = 'ajout';
	var btnColor = 'primary';
	var btnTxt   = 'Valider';

	if (tag!='' && tag.substr(0,1)=='<') {
		action 	 = 'modif';
		btnColor = 'danger';
		btnTxt   = 'Modifier';
	}

	// Ferme le menu contextuel parent
	$('div[idnode="' + id + '"]').remove();

	// Bloc menu contextuel
	$('#' + id).after('<div id="cm_' + id + '" idnode="' + id + '" class="cm_edit" style="width:370px; min-width:370px; left:' + posX + 'px;"></div>');
	var cm = $('#cm_' + id);
	cm.draggable({ axis: 'x' });
	cm.resizable({ handles: 'e, w' });

	menuTagSpeFree_Line0(elem, posX);
	menuTagSpeFree_Line1(elem, posX);
	menuTagSpeFree_Line2(elem, posX);
	menuTagSpeFree_Line2b(elem, posX);
	menuTagSpeFree_Line3(elem, posX);
	menuTagSpeFree_Line4(elem, posX);

	// Champ text : balise HTML
	cm.append('<div id="cm_group_tag_' + id + '" class="input-group col-lg-12" style="display:inline-block; margin-bottom:5px;"></div>');
	var cm_group_tag = $('#cm_group_tag_' + id);

	cm_group_tag.append('<input id="cm_tag_' + id + '" type="text" class="form-control" style="display:inline-block; width:calc(100% - 50px);" aria-describedby="cm_tag">');
	var cm_tag = $('#cm_tag_' + id);
	cm_tag.select();
	cm_tag.keypress(function(e) {
		if (e.keyCode === 13) { if (e.preventDefault) { e.preventDefault(); } tagSpeFree(id, action, sibling);  }
		if (e.keyCode === 27) { if (e.preventDefault) { e.preventDefault(); } cm_close(id); cm_tag_close(id)	}
	});

	cm_group_tag.append('<span id="cm_tag" class="input-group-addon" style="display:inline-block; width:50px; line-height:20px;"></span>');
	$('#cm_tag').append('<span id="cm_tagTxt">' + txtArea + '</tag>');

	// conteneur bottom modal
	cm.append('<div id="cm_tagBottom_' + id + '"></div>');
	var cm_tagBottom = $('#cm_tagBottom_' + id);

	// Message erreur
	cm_tagBottom.append('<div id="cm_tagError_' + id + '" style="margin:5px 0 5px 2px; color:red;" class="pull-left"></div>');

	// Conteneur boutons
	cm_tagBottom.append('<div id="cm_tagEdit_' + id + '" class="pull-right" style="margin:5px 0px 5px 0;"></div>');
	var cm_tagEdit = $('#cm_tagEdit_' + id);

	// Bouton Maj / Min
	cm_tagEdit.append('<input id="cm_tagMajMin_' + id + '" type="button" class="btn btn-default btn-sm pull-left" style="margin-right:5px;" value="Maj" onclick="tagSpeMajMin(\'' + id + '\');">');

	// Boutons (group)
	cm_tagEdit.append('<div id="cm_tagEditGroup_' + id + '" class="btn-group btn-group-sm" role="group" aria-label="cm_l2a"></div>');
	var cm_tagEditGroup = $('#cm_tagEditGroup_' + id);

	// Bouton Annuler
	cm_tagEditGroup.append('<input id="cm_tagCancel_' + id + '" type="button" class="btn btn-default btn-sm" value="Annuler" onclick="cm_close(\'' + id + '\'); cm_tag_close(\'' + id + '\');">');

	// Bouton Valider
	cm_tagEditGroup.append('<input id="cm_tagValid_' + id + '" type="button" class="btn btn-' + btnColor + ' btn-sm" value="' + btnTxt + '" onclick="tagSpeFree(\'' + id + '\', \'' + action + '\', \'' + sibling + '\');">');

	// Modification d'un tag
	if (action == 'modif') {
		cm_tag.val(tag);
	}
}


function tagSpeMajMin(id)
{
	var id;
	var texte;
	var variable;

	var cm_tagMajMin = $('#cm_tagMajMin_' + id);

	// Passage en majuscules
	if (cm_tagMajMin.attr('class') == 'btn btn-default btn-sm pull-left') {

		cm_tagMajMin.attr('class', 'btn btn-primary btn-sm pull-left');
		$('#cm_tag_' + id).focus();

		$('[tagMaj="1"]').each( function(elem) {

			//console.log( $(this).attr('title') );
			//	$('#cm_l3a_' + id).append('<button idobjet="' + id + '" id="cm_l3a_a_reg_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="a_reg"></button>');

			id 			= $(this).attr('id');
			texte 		= $('#' + id + '>span').html();
			variable	= $('#' + id + '').attr('variable');

			$('#' + id + '>span').html(ucfirst(texte));
			$('#' + id + '').attr('variable', ucfirstWithoutAccent(variable));
		});

	// Passage en minuscules
	} else {

		cm_tagMajMin.attr('class', 'btn btn-default btn-sm pull-left');
		$('#cm_tag_' + id).focus();

		$('[tagMaj="1"]').each( function(elem) {

			//console.log( $(this).attr('title') );
			//	$('#cm_l3a_' + id).append('<button idobjet="' + id + '" id="cm_l3a_a_reg_' + id + '" type="button" class="btn btn-default tagSpe autoValid cm-tag-free" tag="spe" variable="a_reg"></button>');

			id 			= $(this).attr('id');
			texte 		= $('#' + id + '>span').html();
			variable	= $('#' + id + '').attr('variable');

			$('#' + id + '>span').html(ucmin(texte));
			$('#' + id + '').attr('variable', ucmin(variable));
		});
	}
}


/**
 * Ajout du texte pour initialiser un spin
 */
$('.spin').on('click', '.add-new-spin', function(event) {

	var parent = $(this).parent();

	// Récupération du nom du champ de spin
	var id = $(this).attr('id');
	id = id.split('_');
	id = id[1];

	// Suppression du bouton d'initialisation
	$(this).remove();

	// Création du textarea
	parent.append('<textarea id="chpInit_' + id + '" style="width:100%;"></textarea>');
	var chpInit = $('#chpInit_' + id);
	chpInit.attr('placeholder', 'Initialisation du spin...');
	chpInit.keypress(function(e) {
		if (e.keyCode === 27) { if (e.preventDefault) { e.preventDefault(); } $('#annulInitSpin_' + id).click(); }
		if (e.keyCode === 13) { if (e.preventDefault) { e.preventDefault(); } $('#validInitSpin_' + id).click(); }
	});

	// Création du groupe de bouton 'Ajouter' | 'Annuler'
	parent.append('<div id="buttonLine_' + id + '" style="display:block; width:100%; height:31px; margin-top:5px; padding-right:1px;"></div>');
	$('#buttonLine_' + id).append('<div id="buttonGrp_' + id + '" class="btn-group pull-right" aria-label="initSpin" role="group">');
	var buttonGrp = $('#buttonGrp_' + id);

	buttonGrp.append('<button id="annulInitSpin_' + id + '" type="button" class="btn btn-default btn-sm annul-init-spin">Annuler</button>');
	buttonGrp.append('<button id="validInitSpin_' + id + '" type="button" class="btn btn-primary btn-sm valid-init-spin">Ajouter</button>');

	chpInit.focus();
});


/**
 * Annulation de l'initialisation d'un spin
 */
$('.spin').on('click', '.annul-init-spin', function(event) {

	var parent = $(this).parent().parent().parent();

	// Récupération du nom du champ de spin
	var id = $(this).attr('id');
	id = id.split('_');
	id = id[1];

	// Ajout du bouton d'initialisation d'un spin
	parent.html('<div id="init_' + id + '" class="fa fa-plus-circle add-new-spin"></div>');
});
