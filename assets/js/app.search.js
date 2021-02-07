/**
 *  @author  Michel Dumont <michel.dumont.io>
 *  @version 1.0.0 [2020-04-10]
 *  @package mediaCenter 1.0.0
 */

/** View => recherche en haut du site */
var SearchView = Backbone.View.extend({
  el: 'body > header',

  /** @var array Années disponibles */
  filterYears: [3000],

  /** @var object Couple {filtreActif: valeur} */
  filtersActive: {},

  /** @var object Events de la vue */
  events: {
    'keyup #search': 'filterCollectionByTitle',
    'change select': 'filterCollectionBySelect',
    'click #toggleFullscreen': 'toggleFullscreen',
    'click #addNewFile': 'openNewFileView'
  },

  /** Rendu de la vue
   *
   * @return object
   */
  render: function () {
    this.populateYearsFilter();

    this.$el
      .html(Tpl.display('assets/views/', 'search', {
        filterYears: this.filterYears
      }))
      .find('input')
      .select()
    return this;
  },

  /** Open an empty view of file
   *
   * @return void
   */
  openNewFileView: function () {
    var model = app.files.add(new FileModel);
    $('#modalWrap')
      .removeClass('active')
      .html(new FileModalView({ model: model }).render().el)
      .delay(100)
      .addClass('active');
  },

  /** Renseigne le filtre année à partir des années disponibles dans la collection
   *
   * @param object
   *
   * @return void
   */
  populateYearsFilter: function () {
    app.files.map(model => {
      if (this.filterYears.indexOf(model.attributes.release_year) == -1) {
        this.filterYears.push(model.attributes.release_year);
      }
    });
    this.filterYears.sort((a, b) => b - a);
  },

  /** Filtre à partir des menus
   *
   * @param event
   *
   * @return void
   */
  filterCollectionBySelect: function (event) {
    if (event.target.value === '' || event.target.value === '3000') {
      this.filtersActive[event.target.dataset.index] = false
    } else {
      this.filtersActive[event.target.dataset.index] = event.target.value
    }

    this.filterCollection();
  },

  /** Filtre par titre
   *
   * @param event
   *
   * @return void
   */
  filterCollectionByTitle: function (event) {
    if (event.keyCode == 27) {
      this.filtersActive.title = false
    } else {
      this.filtersActive.title = new RegExp(event.target.value, "gi")
    }

    this.filterCollection();
  },

  /** Filtre la collection avec les critères établis
   *
   * @return void
   */
  filterCollection: function () {
    app.files.map((model) => model.set({ 'hidden': false }))
    _.map(this.filtersActive, (value, index) => this.filterModel(value, index));
    app.files.trigger('reset');
  },

  /** Filtre un model
   *
   * @param string
   * @param string
   *
   * @return void
   */
  filterModel: function (value, index) {
    if (value !== false) {
      app.files.filter(model => {
        model.set({ 'hidden': (typeof model.attributes[index] == 'undefined') || (value == 'null' && typeof model.attributes[index] != 'object') || (value != 'null' && (typeof model.attributes[index] == 'object' || !model.attributes[index].match(value) || model.attributes.hidden)) })
      })
    }
  },

  /** Check si le contenu est en fullscreen
   *
   * @return boolean
   */
  isFullscrren: function () {
    return (null !== (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement || null));
  },
  /** Active fullscreen
   * @see https://usefulangle.com/post/12/javascript-going-fullscreen-is-rare
   *
   * @param element
   *
   * @return void
   */
  toggleFullscreen: function (event) {
    if (this.isFullscrren()) {
      if (document.exitFullscreen)
        document.exitFullscreen();
      else if (document.mozCancelFullScreen)
        document.mozCancelFullScreen();
      else if (document.webkitExitFullscreen)
        document.webkitExitFullscreen();
      else if (document.msExitFullscreen)
        document.msExitFullscreen();
      $(event.currentTarget).removeClass('fa-compress').addClass('fa-expand');
    } else {
      let element = $('body').get(0);
      if (element.requestFullscreen)
        element.requestFullscreen();
      else if (element.mozRequestFullScreen)
        element.mozRequestFullScreen();
      else if (element.webkitRequestFullscreen)
        element.webkitRequestFullscreen();
      else if (element.msRequestFullscreen)
        element.msRequestFullscreen();
      $(event.currentTarget).removeClass('fa-expand').addClass('fa-compress');
    }
  }
});
