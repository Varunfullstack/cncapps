import React from "react";

const noop = () => {
};

export const FileInput = ({value, onChange = noop, ...rest}) => {
    return (
        <div>
            {Boolean(value.length) && (
                <div>Selected file{value.length > 1 ? 's' : ''}: {value.map(f => f.name).join(", ")}</div>
            )}
            <label>
                Click to select some files...
                <input
                    {...rest}
                    style={{display: "none"}}
                    type="file"
                    onChange={e => {
                        onChange([...e.target.files]);
                    }}
                />
            </label>
        </div>
    )
}

