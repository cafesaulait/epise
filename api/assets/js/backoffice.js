document.addEventListener("DOMContentLoaded", () => {
  const zones = document.querySelectorAll("[data-upload-zone]");

  zones.forEach((zone) => {
    const input = zone.querySelector('input[type="file"]');
    const preview = zone.querySelector("[data-upload-preview]");

    if (!input) {
      return;
    }

    zone.addEventListener("click", () => {
      input.click();
    });

    ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
      zone.addEventListener(eventName, (event) => {
        event.preventDefault();
        event.stopPropagation();
      });
    });

    ["dragenter", "dragover"].forEach((eventName) => {
      zone.addEventListener(eventName, () => {
        zone.classList.add("dragover");
      });
    });

    ["dragleave", "drop"].forEach((eventName) => {
      zone.addEventListener(eventName, () => {
        zone.classList.remove("dragover");
      });
    });

    zone.addEventListener("drop", (event) => {
      const files = event.dataTransfer.files;

      if (!files || files.length === 0) {
        return;
      }

      const file = files[0];

      if (!file.type.startsWith("image/")) {
        alert("Veuillez déposer une image.");
        return;
      }

      input.files = files;

      afficherPreview(file);
    });

    input.addEventListener("change", () => {
      if (input.files && input.files.length > 0) {
        afficherPreview(input.files[0]);
      }
    });

    function afficherPreview(file) {
      if (!preview) {
        return;
      }

      const reader = new FileReader();

      reader.onload = (event) => {
        preview.src = event.target.result;
        preview.hidden = false;

        const contenu = zone.querySelector(".upload-zone-content");

        if (contenu) {
          contenu.style.display = "none";
        }
      };

      reader.readAsDataURL(file);
    }
  });
});
