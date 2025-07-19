function showLoadingScreen() {
  var loadingScreen = document.getElementById('loading-screen');
  loadingScreen.style.display = 'flex';
}


document.addEventListener('DOMContentLoaded', () => {
const emailFocus = document.getElementById('email-focus');
const emailInputBox = document.getElementById('email');
const emailLabel = document.getElementById('email-text');

const passwordLabel = document.getElementById('password-text');
const passwordInputBox = document.getElementById('password');
const passwordFocus = document.getElementById('password-focus');

emailInputBox.addEventListener('focus', () => {
  emailFocus.style.height = '44px';
  emailLabel.style.transform = 'translateY(-34px)';
  emailFocus.style.backgroundColor = 'rgb(50, 50, 50)';
});

emailInputBox.addEventListener('blur', () => {
  if (emailInputBox.value.trim() === '') {
    emailFocus.style.height = '2px';
    emailLabel.style.transform = 'translateY(0px)';
    emailFocus.style.backgroundColor = 'rgb(50, 50, 50)';
  }
});

passwordInputBox.addEventListener('focus', () => {
  passwordFocus.style.height = '44px';
  passwordLabel.style.transform = 'translateY(-34px)';
  passwordFocus.style.backgroundColor = 'rgb(50, 50, 50)';
});

passwordInputBox.addEventListener('blur', () => {
  if (passwordInputBox.value.trim() === '') {
    passwordFocus.style.height = '2px';
    passwordLabel.style.transform = 'translateY(0px)';
    passwordFocus.style.backgroundColor = 'rgb(50, 50, 50)';
  }
});
});