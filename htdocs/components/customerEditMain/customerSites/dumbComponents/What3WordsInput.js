import React from 'react';

const What3WordsInput = ({value, handleChange}) => {
    const isValidWhat3Words = (val) => {
        const regex = /^\w+\.\w+\.\w+$/;
        return regex.exec(val) !== null
    }

    return (
        <tr>
            <td className="content">What3Words</td>
            <td className="content">
                <input title="[word].[word].[word] format required"
                       value={value ? value : ''}
                       pattern="^\w+\.\w+\.\w+$"
                       size="30"
                       className="what3WordsInput"
                       name="what3Words"
                       onChange={handleChange}
                />
                <span className="what3WordsLinkHolder">
                            <a target="_blank"
                               href={isValidWhat3Words(value) ? "https://what3words.com/" + value : '#'}
                            >
                                <img src={isValidWhat3Words(value) ? '/images/w3w_SymbolTransparentBackground_RGB_Black.png' : "/images/w3w_SymbolTransparentBackground_RGB_Red.png"}
                                     height="30"
                                     alt="what3WordsLogo"
                                />
                            </a>
                        </span>
            </td>
        </tr>
    )
}
export default What3WordsInput;