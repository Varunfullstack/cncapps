import React from 'react';

const SiteSelect = ({value, labelText, handleChange, options, name, optionLabelFn}) => {
    return (
        <tr>
            <td className="content">{labelText}</td>
            <td>
                <select
                    value={value ? value : ''}
                    onChange={handleChange}
                    name={name}
                >
                    {
                        options && options.length ?
                            options.map(x => {
                                return <option value={x.id}
                                               key={`${name}-${x.id}`}
                                >{`${optionLabelFn(x)}`}</option>
                            }) :
                            ''
                    }
                </select>
            </td>
        </tr>
    )
}

export default SiteSelect;