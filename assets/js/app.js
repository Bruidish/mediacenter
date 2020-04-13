/**
 *  @author  Michel Dumont <michel.dumont.io>
 *  @version 1.0.0 [2020-04-10]
 *  @package mediaCenter 1.0.0
 */

var app = {

  /** initialize l'application*/
  initialize: function () {

    this.files = new FileCollection();

    this.filesList = new FileListView();
    this.searchForm = new SearchView();

    this.render();
  },

  /** Affiche les différents contenus */
  render: function () {
    this.searchForm.render();
    this.renderFiles();
    this.renderUploader()
  },

  /** Affiche la liste des fichiers disponibles */
  renderFiles: function () {
    $(document).on('keyup', event => {
      if (event.keyCode == 27) {
        $('#modalWrap').removeClass('active');
      }
    })
    this.files.fetch();
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