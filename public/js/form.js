const button = document.getElementById('submit');
const loader = document.getElementById('loader');
const urlInput = document.getElementById('url');

button.addEventListener('click', () => {
    if (_validateInput()) {
        button.style.display = 'none';
        loader.style.display = 'block';
        sendData();
    }
});

function _validateInput()
{
    if (urlInput.value.indexOf('www.niva4x4.ru') === -1) {
        alert('Ссылка должна быть связана с \'www.niva4x4.ru\'');
        return false;
    }
    return true;
}

async function sendData()
{
    const data = {
        url: urlInput.value
    }
    const response = await fetch("/load_statistic.php", {
        method: "POST",
        headers: {
            "Content-Type": "multipart/form-data"
        },
        body: data
    });
    if (!response.ok) {
        alert("Ошибка HTTP: " + response.status);
    }
}