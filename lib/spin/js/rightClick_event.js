// Actions au clic droit ------------------------------------------
(function($)
{
	$(function()
	{
		// Vérouillage du click droit dans le spin
		$('.spin').on('contextmenu', function(event) {
			event.preventDefault();

			// Ferme toutes les actions en cours des autres spin
			initOtherSpin(this);

			/**
			 * Dans le cas d'un spin calculé, on désactive le click droit et donc la modification
			 * s'il est en statut "calcul_on" ou "calcul_end"
			 */
			if (checkStatutSpinCalc(this)) {

				// Actions au clic droit d'un texte
				$('.spin').on('contextmenu', '.on, .off', function(event) {
					var posX = recupPosX(this, event);
					cm_text(this, event, posX);
				});

				// Actions au clic droit sur un spin
				$('.spin').on('contextmenu', '.spin_off, .spin_on', function(event) {
					var posX = recupPosX(this, event);
					cm_spin(this, event, posX);
				});

				// Actions au clic droit sur une liaison
				$('.spin').on('contextmenu', '.link', function(event) {
					var posX = recupPosX(this, event);
					cm_link(this, event, posX);
				});

				// Actions au clic droit d'une balise HTML
				$('.spin').on('contextmenu', '[class*="tag-edit"]', function(event) {
					var posX = recupPosX(this, event);
					cm_tag(this, event, posX);
				});

				// Actions au clic droit d'une balise spécifique (géo, bloc html, etc.)
				$('.spin').on('contextmenu', '[class*="tag-spe"]', function(event) {
					var posX = recupPosX(this, event);
					cm_tag(this, event, posX);
				});
			}
		});

		// Edition d'un bloc texte avec le clic molette
		$('.spin').on('mousedown', '.on, .off', function(event) {

			event.preventDefault();

			/**
			 * Dans le cas d'un spin calculé, on désactive le click molette et donc la modification
			 * s'il est en statut "calcul_on" ou "calcul_end"
			 */
			if (checkStatutSpinCalc(this) && event.which == 2) {

				var id 		 = $(this).attr('id');
				var parent 	 = $(this).parent();
				var idParent = parent.attr('id');

				// Ferme toutes les actions en cours des autres spin
				initOtherSpin(parent);

				// Source (form | modal | fullscreen)
				var source  = parentSource(id);

				// Ferme menu contextuel ouverts
				$('.cm, .cm_edit, .cm_synonymes, .cm_spin').remove();

				// Fermeture des modal de spin de meme niveau
				$('#' + idParent + '>.modalspin').remove();
				$('#' + idParent + '>.spin_on').attr('class', 'spin_off');

				// Déselection des bloc de texte
				textDeselect(this);

				// Sélection du bloc texte
				$(this).attr('class', 'on');

				// Position du clic
				var posX = recupPosX(this, event);

				// Affichage du contextMenus
				cm_editText( $(this).attr('id'), posX );
			}
		});
	});
})(jQuery);
// ----------------------------------------------------------------
