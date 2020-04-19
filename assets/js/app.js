/**
 *  @author  Michel Dumont <michel.dumont.io>
 *  @version 1.0.0 [2020-04-10]
 *  @package mediaCenter 1.0.0
 */

var app = {

  /** initialize l'application*/
  initialize: function () {
    // Collections
    this.files = new FileCollection();

    // Views
    this.filesList = new FileListView();
    this.searchForm = new SearchView();

    // Events
    this.events();

    // Rendering
    this.files.fetch({
      success: () => this.render()
    });
  },

  /** Affiche les différents contenus */
  render: function () {
    this.renderSearch();
    this.renderUploader()
  },

  /** Affiche la liste des fichiers disponibles */
  events: function () {
    $(document).on('keyup', event => {
      switch (event.keyCode) {
        case 27:
          $('#modalWrap overlay').trigger('click');
          break;
        case 37:
          $('#modalWrap .move-left').trigger('click');
          break;
        case 39:
          $('#modalWrap .move-right').trigger('click');
          break;
      }
    })
  },

  /** Affiche le formulaire de recherche et les filtres */
  renderSearch: function () {
    this.searchForm.render();
  },

  /** Affiche la zone de dépos des fichiers pour l'upload */
  renderUploader: function () {
    $('body').on('dragover', function (e) { $(this).addClass('dragOn'); return false; });
    $('body').on('dragleave', function (e) { $(this).removeClass('dragOn'); return false; });
    $('body').on('drop', (event) => this.readFileDroped(event));
  },
  /** Enregistre les fichiers déposés sur le serveur */
  readFileDroped: function (event) {
    event.preventDefault();

    let files = event.originalEvent.dataTransfer.files;

    for (var i = 0; i < files.length; i++) {
      let form = new FormData();
      form.append('file', files[i]);

      let xhr = new XMLHttpRequest();
      xhr.open('POST', `/file/upload`);
      xhr.onload = (event) => {
        $('body').removeClass('dragOn');
        app.files.fetch()
      };
      xhr.send(form);
    }
  }
}