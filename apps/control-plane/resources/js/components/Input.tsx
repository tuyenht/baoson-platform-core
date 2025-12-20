import React, { useState } from 'react';

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
    label: string;
    icon?: React.ReactNode;
    isPassword?: boolean;
    showAtSymbol?: boolean;
}

const Input: React.FC<InputProps> = ({ label, icon, isPassword, showAtSymbol, disabled, ...props }) => {
    const [showPass, setShowPass] = useState(false);

    const hasRightAction = isPassword || showAtSymbol;
    const hasLeftIcon = !!icon;

    return (
        <div className="space-y-1.5">
            <label className={`block text-base font-semibold text-slate-700 ml-1 ${disabled ? 'opacity-50' : ''}`}>
                {label}
            </label>
            <div className="relative group">
                {/* Icon Left */}
                {hasLeftIcon && (
                    <div className={`absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors pointer-events-none ${disabled ? 'opacity-50' : ''}`}>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="w-5 h-5">
                            {icon}
                        </svg>
                    </div>
                )}

                <input 
                    {...props}
                    disabled={disabled}
                    type={isPassword ? (showPass ? 'text' : 'password') : props.type}
                    className={`
                        w-full ${hasLeftIcon ? 'pl-11' : 'pl-3'} ${hasRightAction ? 'pr-11' : 'pr-3'} py-3 
                        bg-slate-50 border border-slate-200 rounded-xl 
                        text-slate-900 text-[15px] font-medium placeholder:text-slate-400
                        outline-none 
                        focus:bg-white focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 
                        transition-all duration-200
                        ${disabled ? 'opacity-50 cursor-not-allowed bg-slate-100' : ''}
                    `}
                />

                {/* Right Actions */}
                {hasRightAction && (
                    <div className="absolute right-3 top-1/2 -translate-y-1/2 flex items-center">
                        {showAtSymbol && (
                            <div className={`w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 text-sm font-semibold select-none ${disabled ? 'opacity-50' : ''}`}>
                                @
                            </div>
                        )}
                        
                        {isPassword && (
                            <button 
                                type="button"
                                disabled={disabled}
                                onClick={() => setShowPass(!showPass)}
                                className={`w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all ${disabled ? 'opacity-50 cursor-not-allowed' : ''}`}
                            >
                                {showPass ? (
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="w-5 h-5">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                        <line x1="1" y1="1" x2="23" y2="23" />
                                    </svg>
                                ) : (
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="w-5 h-5">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                )}
                            </button>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
};

export default Input;
