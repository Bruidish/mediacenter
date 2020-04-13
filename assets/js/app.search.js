/**
 *  @author  Michel Dumont <michel.dumont.io>
 *  @version 1.0.0 [2020-04-10]
 *  @package mediaCenter 1.0.0
 */

/** View => recherche en haut du site */
var SearchView = Backbone.View.extend({
  el: 'body > header',

  render: function () {
    this.$el
      .html(Tpl.display('assets/views/', 'search', {}))
      .find('input')
      .select();
    return this;
  },
  events: {
    'keyup input': 'filterCollection'
  },

  /** Filtre la collection de fichiers
   *
   * @param event
   */
  filterCollection: function (event) {
    if (event.keyCode == 27) {
      event.target.value = '';
    }

    app.files.filter((model) => {
      model.set({ 'hidden': !model.attributes.title.match(new RegExp(event.target.value, "gi")) })
    })
  }
});
