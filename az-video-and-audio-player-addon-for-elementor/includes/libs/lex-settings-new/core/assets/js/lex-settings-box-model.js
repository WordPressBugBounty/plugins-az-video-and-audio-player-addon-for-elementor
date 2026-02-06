/**
 * Box Model Control
 * Adds two-way sync between the CSS shorthand input and the visual side inputs.
 * Default mode is Text; Visual mode can be toggled on demand.
 */
(function (window, document) {
    if (window.LEXboxModel) {
        return;
    }

    const stateMap = new WeakMap();
    const SIDE_ORDER = ['top', 'right', 'bottom', 'left'];
    const BORDER_STYLES = [
        'none',
        'hidden',
        'solid',
        'dashed',
        'dotted',
        'double',
        'groove',
        'ridge',
        'inset',
        'outset',
    ];
    const PLACEHOLDER_BORDER_COLOR = '#dddddd';
    const COLOR_TEST_BASELINE = '#123456';
    const COLOR_TEST_CANVAS = document.createElement('canvas');
    const COLOR_TEST_CONTEXT = COLOR_TEST_CANVAS && COLOR_TEST_CANVAS.getContext
        ? COLOR_TEST_CANVAS.getContext('2d')
        : null;

    function inferDefaultMode(wrapper) {
        if (!wrapper) {
            return 'text';
        }

        if (wrapper.classList.contains('lex-css-input-wrapper--visual-default')) {
            return 'visual';
        }

        if (wrapper.classList.contains('lex-css-input-wrapper--text-default')) {
            return 'text';
        }

        return 'text';
    }

    function normalizeValues(values) {
        return values.map((value) => (value || '').trim());
    }

    function parseTextValue(rawValue) {
        const value = (rawValue || '').trim().replace(/\s+/g, ' ');

        if (!value) {
            return ['', '', '', ''];
        }

        const parts = value.split(' ').filter(Boolean);

        switch (parts.length) {
            case 1:
                return [parts[0], parts[0], parts[0], parts[0]];
            case 2:
                return [parts[0], parts[1], parts[0], parts[1]];
            case 3:
                return [parts[0], parts[1], parts[2], parts[1]];
            default:
                return [parts[0], parts[1], parts[2], parts[3]];
        }
    }

    function stringifyValues(values) {
        const [top, right, bottom, left] = normalizeValues(values);

        if (!top && !right && !bottom && !left) {
            return '';
        }

        if (top === right && top === bottom && top === left) {
            return top;
        }

        if (top === bottom && right === left) {
            return `${top} ${right}`.trim();
        }

        if (right === left) {
            return `${top} ${right} ${bottom}`.trim();
        }

        return [top, right, bottom, left].join(' ').trim();
    }

    function getFieldLabel(wrapper) {
        const label = wrapper.closest('tr')?.querySelector('label');
        return label ? label.textContent.trim() || 'values' : 'values';
    }

    function updateModeUI(state, mode) {
        const isVisual = mode === 'visual';
        const { wrapper, visualWrapper, modeButton, fieldLabel } = state;

        wrapper.dataset.mode = isVisual ? 'visual' : 'text';

        if (visualWrapper) {
            visualWrapper.setAttribute('aria-hidden', isVisual ? 'false' : 'true');
        }

        modeButton.setAttribute('aria-expanded', isVisual ? 'true' : 'false');
        const toggleLabel = isVisual
            ? `Hide visual mode for ${fieldLabel}`
            : `Show visual mode for ${fieldLabel}`;
        modeButton.setAttribute('aria-label', toggleLabel);
        modeButton.setAttribute('title', toggleLabel);
        modeButton.classList.toggle('is-active', isVisual);

        const icon = modeButton.querySelector('.dashicons');
        if (icon) {
            icon.classList.add('dashicons-visibility');
            icon.classList.remove('dashicons-hidden');
        }

        if (state.type === 'border' && state.textInput) {
            if (isVisual) {
                state.textInput.setAttribute('aria-hidden', 'true');
                state.textInput.setAttribute('tabindex', '-1');
            } else {
                state.textInput.removeAttribute('aria-hidden');
                state.textInput.removeAttribute('tabindex');
            }
        }
    }

    function syncTextToVisual(state) {
        if (state.isUpdating) {
            return;
        }

        const { textInput, sideInputs } = state;
        const sides = parseTextValue(textInput.value);
        const normalizedText = stringifyValues(sides);

        state.isUpdating = true;
        sideInputs.forEach((input, index) => {
            input.value = sides[index] || '';
        });

        if (textInput.value.trim() !== normalizedText) {
            textInput.value = normalizedText;
        }

        state.isUpdating = false;
    }

    function syncVisualToText(state) {
        if (state.isUpdating) {
            return;
        }

        const { textInput, sideInputs } = state;
        const values = sideInputs.map((input) => input.value);
        const shorthand = stringifyValues(values);

        state.isUpdating = true;
        textInput.value = shorthand;
        state.isUpdating = false;
    }

    function setMode(state, mode) {
        updateModeUI(state, mode);

        if (mode === 'visual') {
            if (state.type === 'border') {
                syncBorderTextToVisual(state);
            } else {
                syncTextToVisual(state);
            }
        } else {
            if (state.type === 'border') {
                syncBorderVisualToText(state);
            } else {
                syncVisualToText(state);
            }
        }
    }

    function setupWrapper(wrapper) {
        const textInput = wrapper.querySelector('.lex-css-input-wrapper__input');
        const sideInputs = SIDE_ORDER.map((position) => wrapper.querySelector(`.lex-css-input-wrapper__visual-input.${position}`));
        const modeButton = wrapper.querySelector('.lex-css-input-wrapper__mode-btn');
        const visualWrapper = wrapper.querySelector('.lex-css-input-wrapper__visual-wrapper');

        if (!textInput || sideInputs.some((input) => !input) || !modeButton || !visualWrapper) {
            return;
        }

        const state = {
            wrapper,
            textInput,
            sideInputs,
            modeButton,
            visualWrapper,
            fieldLabel: getFieldLabel(wrapper),
            isUpdating: false,
            type: 'box',
        };

        stateMap.set(wrapper, state);

        if (!wrapper.dataset.mode) {
            wrapper.dataset.mode = inferDefaultMode(wrapper);
        }

        updateModeUI(state, wrapper.dataset.mode);
        syncTextToVisual(state);

        textInput.addEventListener('input', () => {
            syncTextToVisual(state);
        });

        sideInputs.forEach((input) => {
            input.addEventListener('input', () => {
                syncVisualToText(state);
            });
            input.addEventListener('change', () => {
                syncVisualToText(state);
            });
        });

        modeButton.addEventListener('click', () => {
            const nextMode = wrapper.dataset.mode === 'text' ? 'visual' : 'text';
            setMode(state, nextMode);
        });
    }

    function tokenizeBorderValue(rawValue) {
        if (!rawValue) {
            return [];
        }

        const value = rawValue.trim();
        if (!value) {
            return [];
        }

        return value.match(/(?:rgba?\([^)]+\)|hsla?\([^)]+\)|var\([^)]+\)|#[0-9a-fA-F]{3,8}|\S+)/g) || [];
    }

    function isBorderWidth(token) {
        if (!token) {
            return false;
        }

        const lower = token.toLowerCase();
        if (['thin', 'medium', 'thick'].includes(lower)) {
            return true;
        }

        return /^(0|[\d.]+(?:(?:px|em|rem|%|vh|vw|ch|cm|mm|in|pt|pc)?))$/i.test(token);
    }

    function componentToHex(component) {
        const clamped = Math.max(0, Math.min(255, Math.round(component)));
        return clamped.toString(16).padStart(2, '0');
    }

    function normalizeColorToken(token) {
        if (!token) {
            return { supported: false, textValue: '', inputValue: '' };
        }

        const trimmed = token.trim();
        if (!trimmed) {
            return { supported: false, textValue: '', inputValue: '' };
        }

        const hex3 = trimmed.match(/^#([0-9a-f]{3})$/i);
        if (hex3) {
            const expanded = `#${hex3[1]
                .split('')
                .map((char) => char + char)
                .join('')}`.toLowerCase();
            return {
                supported: true,
                textValue: expanded,
                inputValue: expanded,
            };
        }

        const hex6 = trimmed.match(/^#([0-9a-f]{6})$/i);
        if (hex6) {
            const normalized = `#${hex6[1].toLowerCase()}`;
            return {
                supported: true,
                textValue: normalized,
                inputValue: normalized,
            };
        }

        if (!COLOR_TEST_CONTEXT) {
            return {
                supported: false,
                textValue: trimmed,
                inputValue: '',
            };
        }

        // Use canvas to validate and normalize the color token.
        const ctx = COLOR_TEST_CONTEXT;
        ctx.fillStyle = COLOR_TEST_BASELINE;
        ctx.fillStyle = trimmed;
        const parsed = ctx.fillStyle;

        const isValid =
            parsed !== COLOR_TEST_BASELINE ||
            trimmed.toLowerCase() === COLOR_TEST_BASELINE.toLowerCase();

        if (!isValid) {
            return {
                supported: false,
                textValue: trimmed,
                inputValue: '',
            };
        }

        if (parsed.startsWith('#')) {
            const normalized = parsed.toLowerCase();
            return {
                supported: true,
                textValue: normalized,
                inputValue: normalized,
            };
        }

        const rgbaMatch = parsed.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)$/i);
        if (rgbaMatch) {
            const alpha = rgbaMatch[4];
            if (alpha && parseFloat(alpha) !== 1) {
                return {
                    supported: false,
                    textValue: trimmed,
                    inputValue: '',
                };
            }

            const r = parseInt(rgbaMatch[1], 10);
            const g = parseInt(rgbaMatch[2], 10);
            const b = parseInt(rgbaMatch[3], 10);
            const hex = `#${componentToHex(r)}${componentToHex(g)}${componentToHex(b)}`;
            return {
                supported: true,
                textValue: hex,
                inputValue: hex,
            };
        }

        return {
            supported: false,
            textValue: trimmed,
            inputValue: '',
        };
    }

    function parseBorderShorthand(rawValue) {
        const tokens = tokenizeBorderValue(rawValue);

        const parsed = {
            width: '',
            style: '',
            color: '',
        };

        tokens.forEach((token) => {
            if (!parsed.style && BORDER_STYLES.includes(token.toLowerCase())) {
                parsed.style = token.toLowerCase();
                return;
            }

            if (!parsed.width && isBorderWidth(token)) {
                parsed.width = token;
                return;
            }

            if (!parsed.color) {
                parsed.color = token;
            }
        });

        return parsed;
    }

    function buildBorderShorthand(values) {
        return [values.width, values.style, values.color].filter(Boolean).join(' ').trim();
    }

    function setupBorderWrapper(wrapper) {
        const textInput = wrapper.querySelector('.lex-css-input-wrapper__input');
        const widthInput = wrapper.querySelector('.lex-css-input-wrapper__border-input.width');
        const styleSelect = wrapper.querySelector('.lex-css-input-wrapper__border-select.style');
        const colorInput = wrapper.querySelector('.lex-css-input-wrapper__border-input.color');
        const modeButton = wrapper.querySelector('.lex-css-input-wrapper__mode-btn');
        const visualWrapper = wrapper.querySelector('.lex-css-input-wrapper__border-wrapper');

        if (!textInput || !widthInput || !styleSelect || !colorInput || !modeButton || !visualWrapper) {
            return;
        }

        const state = {
            wrapper,
            textInput,
            widthInput,
            styleSelect,
            colorInput,
            modeButton,
            visualWrapper,
            fieldLabel: getFieldLabel(wrapper),
            isUpdating: false,
            type: 'border',
            supportsColorInput: true,
            rawColorToken: '',
            isUsingPlaceholderColor: false,
        };

        stateMap.set(wrapper, state);

        if (!wrapper.dataset.mode) {
            wrapper.dataset.mode = inferDefaultMode(wrapper);
        }

        updateModeUI(state, wrapper.dataset.mode);
        syncBorderTextToVisual(state);

        textInput.addEventListener('input', () => {
            syncBorderTextToVisual(state);
        });

        widthInput.addEventListener('input', () => {
            syncBorderVisualToText(state);
        });

        styleSelect.addEventListener('change', () => {
            syncBorderVisualToText(state);
        });

        colorInput.addEventListener('input', () => {
            state.supportsColorInput = true;
            state.rawColorToken = colorInput.value;
            state.isUsingPlaceholderColor = false;
            visualWrapper.classList.remove('lex-css-input-wrapper__border-wrapper--unsupported-color');
            syncBorderVisualToText(state);
        });

        colorInput.addEventListener('change', () => {
            state.supportsColorInput = true;
            state.rawColorToken = colorInput.value;
            state.isUsingPlaceholderColor = false;
            visualWrapper.classList.remove('lex-css-input-wrapper__border-wrapper--unsupported-color');
            syncBorderVisualToText(state);
        });

        modeButton.addEventListener('click', () => {
            const nextMode = wrapper.dataset.mode === 'text' ? 'visual' : 'text';
            setMode(state, nextMode);
        });
    }

    function syncBorderTextToVisual(state) {
        if (state.isUpdating) {
            return;
        }

        const { textInput, widthInput, styleSelect, colorInput, visualWrapper } = state;
        const parsed = parseBorderShorthand(textInput.value);
        state.rawColorToken = parsed.color;

        const normalizedStyle = parsed.style && BORDER_STYLES.includes(parsed.style) ? parsed.style : '';
        const colorInfo = normalizeColorToken(parsed.color);
        const isEmptyValue = !textInput.value.trim();

        if (isEmptyValue) {
            state.isUsingPlaceholderColor = true;
            state.supportsColorInput = true;
            state.rawColorToken = '';

            state.isUpdating = true;
            widthInput.value = '';
            if (styleSelect.querySelector('option[value=""]')) {
                styleSelect.value = '';
            } else if (styleSelect.options.length > 0) {
                styleSelect.selectedIndex = 0;
            }
            colorInput.value = PLACEHOLDER_BORDER_COLOR;
            visualWrapper.classList.remove('lex-css-input-wrapper__border-wrapper--unsupported-color');
            state.isUpdating = false;
            return;
        }

        state.supportsColorInput = colorInfo.supported || !parsed.color;
        state.isUsingPlaceholderColor = false;

        state.isUpdating = true;

        widthInput.value = parsed.width || '';

        if (normalizedStyle) {
            styleSelect.value = normalizedStyle;
        } else if (styleSelect.querySelector('option[value=""]')) {
            styleSelect.value = '';
        } else if (styleSelect.options.length > 0) {
            styleSelect.selectedIndex = 0;
        }

        if (state.supportsColorInput && colorInfo.inputValue) {
            colorInput.value = colorInfo.inputValue;
            state.rawColorToken = colorInfo.textValue;
        }

        visualWrapper.classList.toggle('lex-css-input-wrapper__border-wrapper--unsupported-color', !state.supportsColorInput && !!parsed.color);

        const colorValueForText = state.supportsColorInput ? state.rawColorToken : parsed.color;

        const normalizedShorthand = buildBorderShorthand({
            width: widthInput.value.trim(),
            style: styleSelect.value.trim(),
            color: colorValueForText,
        });

        if (normalizedShorthand !== textInput.value.trim()) {
            textInput.value = normalizedShorthand;
        }

        state.isUpdating = false;
    }

    function syncBorderVisualToText(state) {
        if (state.isUpdating) {
            return;
        }

        const { textInput, widthInput, styleSelect, colorInput, visualWrapper } = state;

        const colorToken = state.supportsColorInput
            ? (state.rawColorToken ? colorInput.value : '')
            : state.rawColorToken;

        const shorthand = buildBorderShorthand({
            width: widthInput.value.trim(),
            style: styleSelect.value.trim(),
            color: colorToken,
        });

        state.isUpdating = true;
        textInput.value = shorthand;
        state.rawColorToken = colorToken;
        state.isUpdating = false;

        if (!shorthand) {
            state.isUsingPlaceholderColor = true;
            state.rawColorToken = '';
            state.supportsColorInput = true;
            widthInput.value = '';
            if (styleSelect.querySelector('option[value=""]')) {
                styleSelect.value = '';
            } else if (styleSelect.options.length > 0) {
                styleSelect.selectedIndex = 0;
            }
            colorInput.value = PLACEHOLDER_BORDER_COLOR;
            visualWrapper.classList.remove('lex-css-input-wrapper__border-wrapper--unsupported-color');
        } else {
            state.isUsingPlaceholderColor = false;
        }
    }

    const LEXBoxModel = {
        initialize(rootElement) {
            const scope = rootElement || document;
            const boxWrappers = scope.querySelectorAll('.lex-field-type--box-model .lex-css-input-wrapper');
            boxWrappers.forEach((wrapper) => setupWrapper(wrapper));

            const borderWrappers = scope.querySelectorAll('.lex-field-type--border .lex-css-input-wrapper');
            borderWrappers.forEach((wrapper) => setupBorderWrapper(wrapper));
        },

        setupBoxModel(wrapper) {
            if (wrapper) {
                setupWrapper(wrapper);
            }
        },

        setupBorder(wrapper) {
            if (wrapper) {
                setupBorderWrapper(wrapper);
            }
        },
    };

    window.LEXboxModel = LEXBoxModel;

    document.addEventListener('DOMContentLoaded', () => {
        LEXBoxModel.initialize(document);
    });
})(window, document);


