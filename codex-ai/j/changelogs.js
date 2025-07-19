let isLoading = false;
let fetchInProgress = false;

const setLoadingState = (state) => {
  isLoading = state;
  const buttonIcon = document.querySelector('#openModalBtn i');
  buttonIcon.className = state
    ? 'fa-solid fa-spinner fa-spin'
    : 'fa-solid fa-clock-rotate-left';
};

const closeModal = () => {
  document.getElementById('changelogModal').style.display = 'none';
};

const openModal = () => {
  const modal = document.getElementById('changelogModal');
  modal.style.display = 'flex';
  modal.style.justifyContent = 'center';
  modal.style.alignItems = 'center';
};

document.getElementById('openModalBtn').onclick = async () => {
  if (fetchInProgress) return;
  try {
    fetchInProgress = true;
    setLoadingState(true);
    await loadChangelog();
    openModal();
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: 'Failed to load changelog.',
    });
  } finally {
    fetchInProgress = false;
    setLoadingState(false);
  }
};

document.querySelector('.close').onclick = closeModal;
window.onclick = (event) => {
  if (event.target === document.getElementById('changelogModal')) closeModal();
};

const loadChangelog = async () => {
  const response = await fetch('https://hag-ai-prochat.onrender.com/changelogs');
  if (!response.ok) throw new Error('Network response was not ok');
  const data = await response.json();
  const changelogList = document.getElementById('changelogList');
  changelogList.innerHTML = data
    .map(
      (commit) => `
        <li>
          <strong>Changes:</strong> ${commit.message}<br>
          <strong>Date:</strong> ${commit.date}
        </li>`
    )
    .join('');
};
