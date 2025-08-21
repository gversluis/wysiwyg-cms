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

       const uploadHandler = function(callback) {
         const editor = tinymce.activeEditor;
         const input = document.createElement('input');
         input.type = 'file';
         input.accept = 'image/*, audio/*, video/*';
         input.onchange = async (e) => {
           const file = e.target.files[0];
           if (!file) return;
           const formData = new FormData();
           formData.append('file', file);

           try {
             const response = await fetch('upload.php', {
               method: 'POST',
               body: formData
             });
             const result = await response.json();
             // expected JSON: { "location": "https://example.com/uploads/myimage.png" }
             if (result.error) alert(result.error);
             else if (result.location && callback) callback(result.location);
             else if (result.location && input.files[0].type.startsWith('image/')) editor.insertContent('<img src="' + result.location + '"/>');
             else if (result.location && input.files[0].type.startsWith('audio/')) editor.insertContent('<audio src="' + result.location + '" controls="true" />');
             else if (result.location && input.files[0].type.startsWith('video/')) editor.insertContent('<video src="' + result.location + '" controls="true" />');
             else alert('Upload failed: '+response.statusText+" "+JSON.stringify(result));
           } catch (err) {
             alert('Upload error: ' + err.message);
           }
         };
         input.click();
      };

      const contentChangedHandler = function(e) {
			  const editor = tinymce.activeEditor;
				if (editor.getContent() != original) {
					if (!document.querySelector('.actions button[name=save]')) {
						addButton('.actions', 'save', function() { 
							submit('edit.php', { edit: window.location.pathname.replace(/\.html?(#.*?)?$/, ''), content: editor.getContent() });
							original=editor.getContent();	// TODO: this assumes everything went well..
							save = document.querySelector('.actions button[name=save]');
							if (save) save.remove();
						}); 
						console.log('Change', editor);
					}
				} else {
					save = document.querySelector('.actions button[name=save]');
					if (save) save.remove();
				}
			};

      tinymce.init({
        selector: '<?=SELECTOR?>',
        license_key: 'gpl',
        setup: function(editor) {
						original = '';
						editor.on('init',function(e){
							original = editor.getContent();
							editor.on('SetContent', contentChangedHandler);
						}),
						editor.on('input', contentChangedHandler);
            editor.on('drop', (e) => {
              e.preventDefault();          // prevent dropping files
              e.stopPropagation();
            });
            editor.on('ExecCommand', (e) => {
              if (e.command === 'mceUpdateImage') {
                // ✅ Runs after content insertion
                const node = editor.selection.getNode();
                console.log('insert content', node);
                // Example: if it's an <audio> file inserted as <a> or <img>, replace with player
                if (node && node.nodeName === 'IMG' && node.src.match(/\.(mp3|ogg)$/i)) {
                  editor.undoManager.transact(() => {
                    editor.dom.replace(editor.dom.create('audio', { controls: true, src: node.src }), node);
                  });
                }
                if (node && node.nodeName === 'IMG' && node.src.match(/\.(mp4)$/i)) {
                  editor.undoManager.transact(() => {
                    editor.dom.replace(editor.dom.create('video', { controls: true, src: node.src }), node);
                  });
                }
              }
            });
            editor.ui.registry.addMenuItem('openfilepicker', {
             text: 'Insert via file picker',
             icon: 'browse',
             onAction: () => {
               const fp = editor.options.get('file_picker_callback');
               if (typeof fp === 'function') {
                 fp((url, info) => {
                   editor.insertContent(`<img src="${url}" alt="${info?.alt || ''}">`);
                 }, '', { filetype: 'image' });
               }
             }
            });
            editor.ui.registry.addMenuItem('insertimage', {
              text: 'Insert image',
              icon: 'image',
              onAction: () => { editor.execCommand('mceImage'); }
            });
				},
        menubar: false,
        inline: true,
        toolbar: false,
        plugins: [ 'link', 'lists', 'searchreplace', 'autolink', 'table', 'quickbars', 'save', 'image' ],
        quickbars_insert_toolbar: false,
        quickbars_selection_toolbar: 'h2 | bold italic underline | forecolor backcolor | image | alignleft aligncenter alignright alignfull | numlist bullist outdent indent | blocks | quicklink',
        contextmenu: 'save | undo redo | insertimage | inserttable | cell row column deletetable | help',
        valid_elements: 'p[style],strong,em,span[style],a[href|class],ul,ol,li,h2,div[class],table,thead,tfoot,tr,td,th,img[src|width|height|border|align|valign],audio[src|controls],video[src|controls]',
        valid_styles: {
          '*': 'font-size,font-family,color,text-decoration,text-align'
        },
        extended_valid_elements: "iframe[src|frameborder|style|scrolling|class|width|height|name|align|allowfullscreen|allow|loading]",
        paste_data_images: false,       // disable pasting images as base64
        images_upload_url: '',           // disables automatic upload
        images_reuse_filename: true,
        image_list: [
          { title: 'My image 1', value: './userfiles/github_profile.jpg' },
          { title: 'My image 2', value: './userfiles/oooo ga960811.png' }
        ],
        image_title: false,
        automatic_uploads: true,
        file_picker_types: 'image',
        file_picker_callback: (cb, value, meta) => {
         uploadHandler(cb, value, meta);
        },
        save_enablewhendirty: false
      });
    });

<?php
    exit;
  } else {
?>
alert('Could not load editor. Not logged in');
<?php
  }
?>

