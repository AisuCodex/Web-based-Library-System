function showLoadingScreen() {
  var loadingScreen = document.getElementById('loading-screen');
  loadingScreen.style.display = 'flex';
}

const emailFocus = document.getElementById('email-focus');
const emailInputBox = document.getElementById('email');
const emailLabel = document.getElementById('email-text');

const passwordLabel = document.getElementById('password-text');
const passwordInputBox = document.getElementById('password');
const passwordFocus = document.getElementById('password-focus');

const hasValue = (input, focus, label) => {
  if (input.value.trim() !== '') {
    focus.style.height = '44px';
    label.style.transform = 'translateY(-34px)';
    focus.style.backgroundColor = 'rgb(50, 50, 50)';
    input.focus();
  }
};
emailInputBox.addEventListener('focus', (e) => {
  e.preventDefault();
  emailFocus.style.height = '44px';
  emailLabel.style.transform = 'translateY(-34px)';
  emailFocus.style.backgroundColor = 'rgb(50, 50, 50)';
});

emailInputBox.addEventListener('blur', (e) => {
  e.preventDefault();
  if (emailInputBox.value.trim() === '') {
    emailFocus.style.height = '2px';
    emailLabel.style.transform = 'translateY(0px)';
    emailFocus.style.backgroundColor = 'rgb(50, 50, 50)';
  }
});

passwordInputBox.addEventListener('focus', (e) => {
  e.preventDefault();
  passwordFocus.style.height = '44px';
  passwordLabel.style.transform = 'translateY(-34px)';
  passwordFocus.style.backgroundColor = 'rgb(50, 50, 50)';
});

passwordInputBox.addEventListener('blur', (e) => {
  e.preventDefault();
  if (passwordInputBox.value.trim() === '') {
    passwordFocus.style.height = '2px';
    passwordLabel.style.transform = 'translateY(0px)';
    passwordFocus.style.backgroundColor = 'rgb(50, 50, 50)';
  }
});

hasValue(emailInputBox, emailFocus, emailLabel);
hasValue(passwordInputBox, passwordFocus, passwordLabel);
