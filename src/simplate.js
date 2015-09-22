
var simplate = function(content) {
    this.content = $(content).html();

    function getStringValue(value) {
        if (value && value.toString) {
            return value.toString();
        }

        return '';
    }

    function hideDepends(data, content) {
        var idx = 0, endIdx = 0,
            pos = 0, endPos = 0,
            expression, showDepend;

        do {
            idx = content.indexOf('{{ depends');
            if (idx > -1) {
                endIdx = content.indexOf('}}', idx);

                if (endIdx < 0) return content;

                // get expression contents
                expression = content.substring(idx + 10, endIdx)
                    .replace(/^\s+/gm, '').replace(/\s+$/gm, '');

                // get end_depends position
                pos = content.indexOf('{{ end_depends', endIdx + 2);
                if (pos < 0) return content;

                endPos = content.indexOf('}}', pos);
                if (endPos < 0) return content;
                endPos += 2;

                $.each(data, function(key, value) {
                    expression = expression.replace(key, value);
                });

                // if expression evals to true then show section, else hide
                try {
                    if (eval(expression) === true) {
                        content = content.substring(0, idx)
                            + content.substring(endIdx + 2, pos)
                            + content.substring(endPos);
                    }
                } catch (exception) {
                    content = content.substring(0, idx)
                        + content.substring(endPos);
                }
            }
        } while (idx !== -1);

        return content;
    }

    function replaceContent(data, content) {
        $.each(data, function(key, value) {
            content = content.replace('{{ ' + key + ' }}', getStringValue(value));
        });

        return content;
    }

    this.render = function(data) {
        return replaceContent(data, hideDepends(data, this.content + ''));
    };

    return this;
}
