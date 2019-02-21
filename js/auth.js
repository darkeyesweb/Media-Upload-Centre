Dropzone.autoDiscover = false;

$.ajax({
  url: 'https://pisscord.org/auth.php?function=check',
  success: (xhr) => {
    if (xhr == "0") {
      window.location.href = "login.html";
    }
  }
});

$(document).ready(() => {
			var DZ = new Dropzone('#uploadform', {
    url: 'https://pisscord.org/upload.php',
    paramName: 'file',
     clickable: true,
    maxFilesize: 2048,
    maxFiles: 1,
    uploadMultiple: false,
    chunking: true,
    parallelChunkUploads: false,
    chunkSize: 5000000,
    retryChunks: true,
    forceChunking: false,
    autoProcessQueue: false,
    forceFallback: false
  });
  DZ.on('sending', function (file, xhr, formData) {
    formData.append('category', $("#category").val());
    formData.append('title', $("#title").val());
    if ($("#anon").prop("checked") === true){
    	var anonState = 1;
    } else {
    	var anonState = 0;
    }
    formData.append('anon', anonState);
    formData.append('description', $("#description").val());
  });
  DZ.on("success", function (file, r) {
    alert(r);
  });
  DZ.on("complete", function(file) {
  DZ.removeFile(file);
  $("#category, #title, #description").val("");
});
});

function upload () {
	if ($("#category").val() != "") {
		if ($("#title").val() != "" && /^[a-zA-Z0-9_ ]*$/.test($("#title").val())) {	Dropzone.forElement(".dropzone").processQueue();
		}  else {
			alert("Title is blank or isn't valid (title can only be composed alphanumeric, spaces, and underscores)");
		}
	} else {
		alert("Pick a category");
	}
  }
