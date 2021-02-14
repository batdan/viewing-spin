/**
 * Ajout d'une balise HTML (tag)
 */
$('.spin').on('click', '.cm-tag', function(event) {

    var tag      = $(this).attr('tag');
    var close    = $(this).attr('close');

    var id       = $(this).attr('idobjet');

    // Source (form | modal | fullscreen)
    var source   = parentSource(id);

	var idParent = $('#' + id).parent().attr('id');
	var spinid 	 = $('#' + idParent).attr('spinid');
	var lastmodif= $('#' + idParent).attr('lastmodif');

	var elem1 	 = $('#' + idParent + '>div[class="on"], #' + idParent + '>div[class="spin_on"]').get(0);
    var elemid1;
    if (!elem1) {

        if (source == 'modal') {

            elem1   = $('#' + id);
            elemid1  = cleanId( $(elem1).attr('id') );

        } else {

            elem1   = $(this).attr('id');
            elem1   = $(this).attr('id').split('__');
            elemid1 = elem1[1];
            var pre = elem1[0].split('_');

            for (var i=0; i<pre.length; i++) {
                elem1 = pre[i];
            }
            elem1 = $('#' + elem1 + '__' + elemid1);
        }

    } else {
        elemid1  = cleanId( $(elem1).attr('id') );
    }

	var prefix	 = prefixId( $(elem1).attr('id') );

	var elemid2  = '';
	if ($('#' + idParent + '>div[class="on"]').length == 2) {
		var elem2 	= $('#' + idParent + '>div[class="on"]').get(1);
		var elemid2 = cleanId( $(elem2).attr('id') );
	}

	$.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'addTag',
        tag         : tag,
        close       : close,
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
});


/**
 * Ajout d'une balise HTML libre (tag)
 */
function tagFree(id, action)
{
    var elem = $('#cm_tag_' + id)
    var html = elem.val();
    var dom  = $.parseHTML(html);
    var err;

    // Champ vide ou 0 balise ou 1 balise de type 'TextNode' (3)
    if (!dom || dom.length==0 || (dom.length == 1 && dom[0].nodeType == 3) ) {
        err = 'Balise HTML absente';
    } else if (dom.length > 1) {
        err = 'Une seule balise HTML';
    } else {
        err = false;
    }

    if (err) {

        $('#cm_tagTxt, #cm_tagFinTxt').attr('style', 'color:red;');
        $('#cm_tagError_' + id).html(err);

        window.setTimeout(function() {
            $('#cm_tagTxt, #cm_tagFinTxt').removeAttr('style');
            $('#cm_tagError_' + id).html('');
        }, 3000);

    } else {

        $('#cm_tagTxt, #cm_tagFinTxt').removeAttr('style');

        // Récupération du nodeName
        var tag = $(dom[0]).prop("tagName").toLowerCase();

        // Récupération de liste des attributs - getAttributes -> tools.js
        var attributes = $(dom[0]).getAttributes();

        // Source (form | modal | fullscreen)
        var source   = parentSource(id);

        var idParent = $('#' + id).parent().attr('id');
        var spinid 	 = $('#' + idParent).attr('spinid');
        var lastmodif= $('#' + idParent).attr('lastmodif');

        var elem1 	 = $('#' + idParent + '>div[class="on"], #' + idParent + '>div[class="spin_on"]').get(0);
        var elemid1;
        if (!elem1) {

            if (source == 'modal') {

                elem1   = $('#' + id);
                elemid1  = cleanId( $(elem1).attr('id') );

            } else {

                elem1   = id.split('__');
                elemid1 = elem1[1];

                var pre = elem1[0];

                elem1 = $('#' + pre + '__' + elemid1);
            }

        } else {
            elemid1  = cleanId( $(elem1).attr('id') );
        }

        var prefix	 = prefixId( $(elem1).attr('id') );

        var elemid2  = '';
        if ($('#' + idParent + '>div[class="on"]').length == 2) {
            var elem2 	= $('#' + idParent + '>div[class="on"]').get(1);
            var elemid2 = cleanId( $(elem2).attr('id') );
        }

        // Conversion en JSON de la liste des attributs
        var JsonAttributes = JSON.stringify(attributes);

        $.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
    	{
    		action		: 'tagFree',
    		action2		: action,
            tag         : tag,
            attributes  : JsonAttributes,
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
}


/**
 * Ajout d'une balise HTML libre (tag)
 */
function tagSpeFree(id, action, sibling)
{
    var elem = $('#cm_tag_' + id);
    var html = elem.val();
    var dom  = $.parseHTML(html);
    var err;

    // Champ vide ou 0 balise ou 1 balise de type 'TextNode' (3)
    if (!dom || dom.length==0 || (dom.length == 1 && dom[0].nodeType == 3) ) {
        err = 'Balise HTML absente';
    } else if (dom.length > 1) {
        err = 'Une seule balise HTML';
    } else {
        err = false;
    }

    if (err) {

        $('#cm_tagTxt, #cm_tagFinTxt').attr('style', 'color:red;');
        $('#cm_tagError_' + id).html(err);

        window.setTimeout(function() {
            $('#cm_tagTxt, #cm_tagFinTxt').removeAttr('style');
            $('#cm_tagError_' + id).html('');
        }, 3000);

    } else {

        $('#cm_tagTxt, #cm_tagFinTxt').removeAttr('style');

        // Récupération du nodeName
        var tag = $(dom[0]).prop("tagName").toLowerCase();

        // Récupération de liste des attributs - getAttributes -> tools.js
        var attributes = $(dom[0]).getAttributes();

        // Source (form | modal | fullscreen)
        var source   = parentSource(id);

        var idParent = $('#' + id).parent().attr('id');
        var spinid 	 = $('#' + idParent).attr('spinid');
        var lastmodif= $('#' + idParent).attr('lastmodif');

        var elem1 	 = $('#' + idParent + '>div[class="on"], #' + idParent + '>div[class="spin_on"]').get(0);
        var elemid1;

        if (!elem1) {

            if (source == 'modal') {

                elem1   = $('#' + id);
                elemid1  = cleanId( $(elem1).attr('id') );

            } else {

                elem1   = id.split('__');
                elemid1 = elem1[1];

                var pre = elem1[0];

                elem1 = $('#' + pre + '__' + elemid1);
            }

        } else {

            elemid1  = cleanId( $(elem1).attr('id') );
        }



        var prefix	 = prefixId( $(elem1).attr('id') );

        var elemid2  = '';
        if ($('#' + idParent + '>div[class="on"]').length == 2) {
            var elem2 	= $('#' + idParent + '>div[class="on"]').get(1);
            var elemid2 = cleanId( $(elem2).attr('id') );
        }

        // Conversion en JSON de la liste des attributs
        var JsonAttributes = JSON.stringify(attributes);

        $.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
    	{
    		action		: 'tagSpeFree',
    		action2		: action,
            tag         : tag,
            sibling     : sibling,
            attributes  : JsonAttributes,
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
}


/**
 * Edition d'un balise HTML (tag)
 */
function editTag(id, posX)
{
    // Source (form | modal | fullscreen)
    var source  = parentSource(id);

	var idParent = $('#' + id).parent().attr('id');
    var spinid 	 = $('#' + idParent).attr('spinid');
	var lastmodif= $('#' + idParent).attr('lastmodif');

    var elemid1 = cleanId(id);

    $.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'editTag',
        spinid		: spinid,
        lastmodif	: lastmodif,
		elemid1		: elemid1
	},
	function success(data)
	{
		// console.log(data);

        // Ouverture de la modal d'édition
        if (data.other.balise.substr(0, 4) == '<spe') {
            cm_tag_spe_free(id, posX, '', data.other.balise)
        } else {
            cm_tag_free(id, posX, data.other.balise);
        }

	}, 'json');
}


/**
 * Suppression d'une balise (tag)
 */
function deleteTag(id)
{
    // Source (form | modal | fullscreen)
    var source  = parentSource(id);

	var idParent = $('#' + id).parent().attr('id');
	var spinid 	 = $('#' + idParent).attr('spinid');
	var lastmodif= $('#' + idParent).attr('lastmodif');

	var elemid1  = cleanId(id);

	$.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'deleteTag',
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
