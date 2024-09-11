<template>
  <div>
    <!-- Upload Area -->
    <div 
      class="upload-area border border-primary rounded p-4 text-center" 
      @drop.prevent="handleDrop" 
      @dragover.prevent
    >
      <input type="file" @change="handleFileSelect" ref="fileInput" hidden />
      <p class="mb-2">Drag & Drop your files here, or</p>
      <button class="btn btn-primary" @click="selectFile">Click to Upload</button>
    </div>

    <!-- File Info and Actions -->
    <div v-if="file" class="mt-4">
      <div class="alert alert-info">
        <p class="mb-0"><strong>Selected File:</strong> {{ file.name }}</p>
      </div>
      <button class="btn btn-success me-2" @click="startProcessing">Start Processing</button>
      <button class="btn btn-secondary" @click="cancel">Cancel</button>
    </div>

    <!-- Result -->
    <div v-if="result" class="mt-4">
      <!-- OCR Result -->
      <div class="alert alert-success">
        <h3 class="mb-3">Extracted Text</h3>
        <p>{{ result.text }}</p>
        <button class="btn btn-primary" @click="copyToClipboard">Copy Text</button>
      </div>
    </div>

    <!-- Daily Limit Reached -->
    <div v-if="fileLimitReached" class="mt-4">
      <div class="alert alert-danger">
        <p class="mb-0">You have reached your daily limit of 5 file processes.</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      file: null,
      processingStatus: null,
      result: null,
      fileLimitReached: false,
    };
  },
  methods: {
    selectFile() {
      this.$refs.fileInput.click();
    },
    handleFileSelect(event) {
      this.file = event.target.files[0];
    },
    async startProcessing() {
      if (!this.file) return;

      const formData = new FormData();
      formData.append('file', this.file);

      try {
        this.processingStatus = 'Processing...';
        this.showToast(this.processingStatus, 'info');

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch('/process-file', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
          },
          body: formData,
        });

        if (response.status === 429) {
          this.fileLimitReached = true;
          this.processingStatus = 'You have reached your daily limit of 5 file processes.';
          this.showToast(this.processingStatus, 'danger');
          return;
        }

        if (response.ok) {
          const result = await response.json();
          this.result = result;
          this.processingStatus = 'Completed';
          this.showToast(this.processingStatus, 'success');
        } else {
          this.processingStatus = 'Failed to process file';
          this.showToast(this.processingStatus, 'danger');
        }
      } catch (error) {
        console.error('Error:', error);
        this.processingStatus = 'Error during file processing';
        this.showToast(this.processingStatus, 'danger');
      }
    },
    cancel() {
      this.file = null;
      this.processingStatus = null;
      this.result = null;
      this.fileLimitReached = false;
    },
    copyToClipboard() {
      if (this.result && this.result.text) {
        navigator.clipboard.writeText(this.result.text)
          .then(() => this.showToast('Text copied to clipboard!', 'success'))
          .catch(err => {
            console.error('Failed to copy text:', err);
            this.showToast('Failed to copy text', 'danger');
          });
      }
    },
    handleDrop(event) {
      this.file = event.dataTransfer.files[0];
    },
    showToast(message, type) {
      const toastContainer = document.getElementById('toast-container');
      if (toastContainer) {
        const toastHTML = `
          <div class="toast fade show text-bg-${type}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-body">
              ${message}
            </div>
          </div>
        `;
        toastContainer.innerHTML = toastHTML;
        setTimeout(() => {
          const toastElement = toastContainer.querySelector('.toast');
          if (toastElement) {
            toastElement.classList.remove('show');
          }
        }, 5000); // Hide toast after 5 seconds
      }
    }
  }
};
</script>
