/**
 * Rendu HTML du champ principal
 */
function loadHTML(id)
{
    var spinid 		= $('#' + id).attr('spinid');
    var lastmodif 	= $('#' + id).attr('lastmodif');

    $.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'renduHTML',
		spinid		: spinid,
		lastmodif	: lastmodif
	},
	function success(data)
	{
		// console.log(data);

		$('#' + id).html(data.html);

        // Gestion des infobulles (tooltip)
        majTooltip();

	}, 'json');
}


/**
 * Mise à jour du rendu avec le bouton refresh
 */
function refreshRendu(spinid, lastmodif)
{
    var id = $('div[spinid="' + spinid + '"]').attr('id');

    $.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
	{
		action		: 'renduHTML',
		spinid		: spinid,
		lastmodif	: lastmodif
	},
	function success(data)
	{
		// console.log(data);

		$('#' + id).html(data.html);

        // Gestion des infobulles (tooltip)
        majTooltip();
        $('.tooltip').remove();

	}, 'json');
}



/**
 * Mise à jour des éditeurs de spin après une sauvegarde
 */
function majAfterSave(id)
{
    var spinid 		= $('#' + id).attr('spinid');

    $.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
    {
        action		: 'majAfterSave',
        spinid		: spinid
    },
    function success(data)
    {
        // console.log(data);

        $('#' + id).html(data.html);

        // Mise à jour des date et heure de modification d'un spin
        majLastmodif(data.spinid, data.lastmodif);

        // Mise à jour du statut s'il s'agit d'un spin calculé
        majStatut(data.spinid, data.statut)

        // Gestion des infobulles (tooltip)
        majTooltip();

    }, 'json');
}


/**
 * Rendu HTML d'une modal d'édition de spin
 */
function loadModalHTML(id)
{
    var idParent    = $('#' + id).parent().attr('id');

    var spinid 		= $('#' + idParent).attr('spinid');
    var lastmodif 	= $('#' + idParent).attr('lastmodif');
    var elemid1     = cleanId(id);

    var idModal = id;

    // Gestion des modals de niveau 2 et plus
	if (id.split('--').length == 2) {
		idModal = id.split('--');
		idModal = idModal[1];
	}

    var idSpinComb = 'modal__' + elemid1 + '--' +  idModal;

    $.post("/vendor/vw/spin/lib/spin/ajax/ajax_rendu.php",
    {
        action		: 'renduModalHTML',
        spinid		: spinid,
        lastmodif	: lastmodif,
        elemid1		: elemid1
    },
    function success(data)
    {
        //console.log(data);

		$('#' + idSpinComb).html(data.html);

        // Gestion des infobulles (tooltip)
        majTooltip();

    }, 'json');
}


/**
 * Fancybox en plein ecran
 */
(function($)
{
 	$(function()
 	{
        $(".spin").on('click', '.fullScreenModal', function() {

            var fancyWidth  = '90%';
            var fancyHeight = '90%';

            if ( $(this).attr('fancy-width') ) {
                fancyWidth = $(this).attr('fancy-width');
            }
            if ( $(this).attr('fancy-height') ) {
                fancyHeight = $(this).attr('fancy-height');
            }

            $.fancybox({
                type        : $(this).attr('fancy-type'),
                href        : $(this).attr('fancy-href'),
                width		: fancyWidth,
                height		: fancyHeight,
                padding		: 0,
                margin		: 0,
                scrolling	: 'no',
                closeBtn	: false,
                fitToView	: false,
                autoSize	: false,
                closeClick	: false,
                openEffect	: 'fade',
                closeEffect	: 'fade',
                key : {
                        close : [27]
                }
            });
        });
    });
})(jQuery);
