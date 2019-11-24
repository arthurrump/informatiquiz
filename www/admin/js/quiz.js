// Adds an new option to the multiple choice list
function addOption(el) {
    let answers = document.getElementsByClassName('answer');
    let item = answers[0].cloneNode(true);

    // Change the value of this input
    item.firstElementChild.setAttribute('value', answers.length + 1);

    // Remove any content from the 'template'
    item.lastElementChild.firstElementChild.value = "";

    el.parentNode.insertBefore(item, el);
}