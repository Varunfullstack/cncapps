import React from 'react';

const SiteInput = ({labelWidth, inputWidth, value, size, maxLength, name, required = false, labelText, type = 'text', handleChange, className, checked}) => {
    return (
        <tr>
            <td className="content"
                width={labelWidth}
            >{labelText}
            </td>
            <td className="content"
                width={inputWidth}
            >
                <input value={value ? value : ''}
                       size={size}
                       maxLength={maxLength}
                       name={name}
                       required={required}
                       type={type}
                       onChange={handleChange}
                       className={className}
                       checked={checked}
                />
            </td>
        </tr>
    )
}

export default SiteInput;