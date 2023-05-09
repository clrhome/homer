function makeInsertCharacter(textarea, characterUnicode) {
  return function () {
    if (window.getSelection != null) {
      var selectionStart = textarea.selectionStart;
      textarea.value =
        textarea.value.slice(0, selectionStart) +
        characterUnicode +
        textarea.value.slice(textarea.selectionEnd);
      textarea.setSelectionRange(selectionStart + 1, selectionStart + 1);
    } else {
      textarea.focus();
      var range = document.selection.createRange();
      range.text = characterUnicode;
    }

    textarea.focus();
  };
}

function initializeCharacterMenu() {
  var characters = [
{foreach from=$characters item=character}    {
{if $character.encoded}      encoded: "{$character.encoded}",
{/if}      ti: {$character.ti},
      unicode: {$character.unicode}
    },
{/foreach}  ];

  var characterMenu = document.createElement("div");
  var textarea = document.getElementsByTagName("textarea").item(0);

  for (
    var characterIndex = 0;
    characterIndex < characters.length;
    characterIndex++
  ) {
    var characterUnicode = String.fromCharCode(
      characters[characterIndex].unicode
    );

    var characterTi = String.fromCharCode(characters[characterIndex].ti);
    var character = document.createElement("a");

    if ("encoded" in characters[characterIndex]) {
      var characterImage = document.createElement("img");
      var encoded = characters[characterIndex].encoded;

      characterImage.src = "?bg_color=333333&fg_color=999933&q=%" + encoded;
      character.appendChild(characterImage);
      character.style.backgroundImage =
        "url('?bg_color=333333&fg_color=666666&q=%" + encoded + "')";
    } else {
      character.appendChild(document.createTextNode(characterTi));
    }

    character.onclick = makeInsertCharacter(textarea, characterUnicode);
    characterMenu.appendChild(character);
    characterMenu.appendChild(document.createTextNode(' '));
  }

  document.getElementsByTagName("div").item(0).appendChild(characterMenu);
}

document.addEventListener("DOMContentLoaded", initializeCharacterMenu);
