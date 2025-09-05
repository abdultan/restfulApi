import React, { useEffect } from "react";
import { useSearchParams, Link } from "react-router-dom";
import { useMutation } from "@tanstack/react-query";
import { authApi } from "../../api/auth";
import { Card, CardContent } from "../../components/ui/card";
import { Button } from "../../components/ui/button";
import { LoadingSpinner } from "../../components/common/LoadingSpinner";

export function VerifyEmail() {
    const [params] = useSearchParams();
    const email = params.get("email") || "";
    const token = params.get("token") || "";

    const verify = useMutation({
        mutationFn: () => authApi.verifyEmail(email, token),
    });

    useEffect(() => {
        if (email && token) {
            verify.mutate();
        }
    }, [email, token]);

    return (
        <div className="min-h-screen flex items-center justify-center p-6">
            <Card className="w-full max-w-md text-center">
                <CardContent className="p-8 space-y-4">
                    {verify.isPending && (
                        <>
                            <LoadingSpinner />
                            <p>E-postanız doğrulanıyor...</p>
                        </>
                    )}
                    {verify.isSuccess && (
                        <>
                            <h2 className="text-xl font-semibold text-green-600">
                                E-posta doğrulandı!
                            </h2>
                            <p>Artık giriş yapabilirsiniz.</p>
                            <Button asChild>
                                <Link to="/login">Giriş Yap</Link>
                            </Button>
                        </>
                    )}
                    {verify.isError && (
                        <>
                            <h2 className="text-xl font-semibold text-red-600">
                                Doğrulama başarısız
                            </h2>
                            <p>Lütfen yeni bir doğrulama bağlantısı isteyin.</p>
                            <Button asChild variant="outline">
                                <Link to="/register">Kayıt Ol</Link>
                            </Button>
                        </>
                    )}
                </CardContent>
            </Card>
        </div>
    );
}

export default VerifyEmail;
