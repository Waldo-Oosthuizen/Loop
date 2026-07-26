// Form validation
document
  .getElementById('registerForm')
  .addEventListener('submit', function (event) {
    // reset errors when users click submit
    let hasErrors = false;
    let errorHtml = '';
    const errorContainer = document.getElementById('js-errors');
    errorContainer.style.display = 'none'; // hide error

    // Validate password length
    const password = document.getElementById('password').value;
    if (password.length < 5) {
      errorHtml += 'Password must be at least 5 characters long.<br>';
      hasErrors = true;
    }

    // Validate file size for profile pic upload
    const fileInput = document.getElementById('profile_picture');
    if (fileInput.files.length > 0) {
      const fileSize = fileInput.files[0].size; // Size in bytes
      const maxSize = 2097152; // 2MB in bytes

      if (fileSize > maxSize) {
        errorHtml += '• Profile picture must be smaller than 2MB.<br>';
        hasErrors = true;
      }
    }

    // If there are errors, stop the form from submitting
    if (hasErrors) {
      event.preventDefault(); // submission not allowed
      errorContainer.innerHTML = errorHtml;
      errorContainer.style.display = 'block'; // display the error

      // Scroll up so the user can see the errors
      window.scrollTo({
        top: 0,
        behavior: 'smooth',
      });
    }
  });
