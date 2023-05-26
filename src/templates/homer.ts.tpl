function makeInsertCharacter(
  textarea: HTMLTextAreaElement,
  characterUnicode: string
): () => void {
  return function (): void {
    if (window.getSelection != null) {
      const selectionStart = textarea.selectionStart;
      textarea.value =
        textarea.value.slice(0, selectionStart) +
        characterUnicode +
        textarea.value.slice(textarea.selectionEnd);
      textarea.setSelectionRange(selectionStart + 1, selectionStart + 1);
    } else {
      textarea.focus();
      // @ts-expect-error
      document.selection.createRange().text = characterUnicode;
    }

    textarea.focus();
  };
}

function initializeCharacterMenu(): void {
  const characters = [
{foreach from=$characters item=character}    {
{if $character.encoded}      encoded: "{$character.encoded}",
{/if}      ti: {$character.ti},
      unicode: {$character.unicode}
    },
{/foreach}  ];

  const characterMenu = document.createElement("div");
  const textarea = document.getElementsByTagName("textarea").item(0)!;

  for (const character of characters) {
    const characterUnicode = String.fromCharCode(character.unicode);
    const characterTi = String.fromCharCode(character.ti);
    const characterButton = document.createElement("a");

    if ("encoded" in character) {
      const characterImage = document.createElement("img");

      characterImage.src = `?bg_color=272722&fg_color=aa9e39&q=%${ldelim}character.encoded{rdelim}`;
      characterButton.appendChild(characterImage);
      characterButton.style.backgroundImage = `url('?bg_color=272722&fg_color=5e5d57&q=%${ldelim}character.encoded{rdelim}')`;
    } else {
      characterButton.appendChild(document.createTextNode(characterTi));
    }

    characterButton.onclick = makeInsertCharacter(textarea, characterUnicode);
    characterMenu.appendChild(characterButton);
    characterMenu.appendChild(document.createTextNode(' '));
  }

  document.getElementsByTagName("div").item(0)!.appendChild(characterMenu);
}

document.addEventListener("DOMContentLoaded", initializeCharacterMenu);
