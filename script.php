<?php	
	include('config.php');
  if (session_start() && $_SESSION['username']) {
?>
    let submit = function(url, data, callback) {
      let formData = new URLSearchParams();
      for (const [key, value] of Object.entries(data)) {
        formData.append(key, value);
      }
      console.log(formData);
      fetch(url, {
        method: "POST",
        credentials: "same-origin",
        body: formData
      }).then(res => {
        if (callback) {
          callback(res);
        }
      });
    };

    let addButton = function(target, name, callback) {
      let targetElement = document.querySelector(target);
      let button = document.createElement("button");
      button.name = name;
      button.innerText = name;
      button.onclick = callback;
      targetElement.appendChild(button);
    };

    addButton('.actions', 'logout', function() { submit('edit.php', { logout: 'logout' }, function() { window.location.reload();}); });

    loadScript('tinymce/js/tinymce/tinymce.min.js', function() {
      tinymce.init({
        selector: '<?=SELECTOR?>',
        license_key: 'gpl',
        setup: function(editor) {
                 addButton('.actions', 'save', function() { 
                   submit('edit.php', { edit: window.location.pathname.replace(/\.html?(#.*?)?$/, ''), content: editor.getContent() }) 
                 }); 
						     console.log('the content ', editor.getContent());
               },
        menubar: false,
        inline: true,
        toolbar: false,
        plugins: [ 'link', 'lists', 'searchreplace', 'autolink', 'table', 'quickbars', 'save' ],
        quickbars_selection_toolbar: 'h2 | bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignfull | numlist bullist outdent indent | blocks | quicklink',
        contextmenu: 'save | undo redo | inserttable | cell row column deletetable | help',
        valid_elements: 'p[style],strong,em,span[style],a[href|class],ul,ol,li,h2,div[class],table,thead,tfoot,tr,td,th',
        valid_styles: {
          '*': 'font-size,font-family,color,text-decoration,text-align'
        },
        save_enablewhendirty: false
      });
    });

<?php
    exit;
  }
?>
alert('Could not load editor. Not logged in');

