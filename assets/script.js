document.addEventListener('DOMContentLoaded', function() {
    var items = document.getElementsByClassName('faq-accordion-item-title');
    console.log(items.length + ' FAQ items found.')
    for (var i = 0; i < items.length; i++) {
        items[i].addEventListener('click', function() {
            this.classList.toggle('active');
            var content = this.nextElementSibling;
            content.classList.toggle('active');
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
            } else {
                content.style.maxHeight = content.scrollHeight + 'px';
            } 
        });
    }
});